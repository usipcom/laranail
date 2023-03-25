<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Livewire\Traits;

use Exception;

trait HasLivewireWizard
{

    // Session Name
    protected string $wizardSessionName = 'livewire_wizard';

    protected int    $defaultWizardStep = 1;

    protected function getStepsSessionName(?string $key = null):string
    {
        return 'steps' . (!empty($key) ? ".$key" : '');
    }

    /*
    |--------------------------------------------------------------------------
    | Wizard ID
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * @param string $wizardId
     * @return self
     */
    private function setupWizardId(string $wizardId): self
    {
        $this->addToWizardSession('id', $wizardId);

        return $this;
    }

    /**
     * @return string
     */
    public function getWizardId(): string
    {
        return $this->getFromWizardSession('id');
    }


    /*
    |--------------------------------------------------------------------------
    | Wizard Menu Configuration
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * @param array $wizardMenu
     * @return static
     */
    private function setupWizardMenu(array $wizardMenu): static
    {

        $this->addToWizardSession($this->getStepsSessionName('menu'), $wizardMenu);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWizardMenu(): mixed
    {
        $wizardMenu = $this->getFromWizardSession($this->getStepsSessionName('menu'));

        if (is_array($wizardMenu) && isset($wizardMenu[0]))
        {
            $wizardMenu = $wizardMenu[0];
        }

        return $wizardMenu;
    }

    /*
    |--------------------------------------------------------------------------
    | Wizard Steps configuration
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * @throws Exception
     */
    protected function initializeWizardSteps(string $wizardId)
    {

        $this->resetWizard();
        $this->setupWizardId($wizardId);
        $this->setCurrentWizardStep($this->defaultWizardStep);

        // get and set wizard menu
        if (method_exists($this, 'setWizardMenu')) {
            $this->setupWizardMenu($this->setWizardMenu());
        }

        if (method_exists($this, 'setWizardStepsData')) {
            // we add 1 step to be used for submission
            $stepsData  = $this->setWizardStepsData();
            $totalSteps = count($stepsData) + 1;
            $this->setMaxWizardSteps($totalSteps);

            // push to session all wizard steps
            foreach (array_keys($stepsData) as $i => $step){
                $this->setWizardStepStatus(false, $step);
            }

            $this->addToWizardSession('time', time());

            $this->setupWizardStepsData($stepsData);

        }else{
            throw new Exception('The method setWizardStepsData() does not exist in the parent class');
        }

        return $this;
    }


    /**
     * @param array $wizardStepsData
     * @return static
     */
    private function setupWizardStepsData(array $wizardStepsData): static
    {
        $this->addToWizardSession($this->getStepsSessionName('all'), $wizardStepsData);

        return $this;
    }

    /**
     * @param int $currentWizardStep
     * @return self
     */
    public function setCurrentWizardStep(int $currentWizardStep): self
    {
        $this->addToWizardSession($this->getStepsSessionName('current'), $currentWizardStep);

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrentWizardStep(): mixed
    {
        return $this->getFromWizardSession($this->getStepsSessionName('current'));
    }

    /**
     * @param int $completedWizardStep
     * @param bool $status
     * @return self
     */
    public function setCompletedWizardStep(int $completedWizardStep, bool $status): self
    {

        $this->addToWizardSession($this->getStepsSessionName("completed.$completedWizardStep"), $status);

        return $this;
    }

    /**
     * @return string
     */
    public function getCompletedWizardStep(): mixed
    {
        return $this->getFromWizardSession($this->getStepsSessionName('completed'));
    }

    /**
     * @param int $total
     * @return self
     */
    public function setMaxWizardSteps(int $total): self
    {

        $this->addToWizardSession($this->getStepsSessionName('max'), $total);

        return $this;
    }

    /**
     * @return string
     */
    public function getMaxWizardSteps(): mixed
    {
        return $this->getFromWizardSession($this->getStepsSessionName('max'));
    }

    /**
     * @return array
     */
    public function getWizardStepsData($key = null, $strict = true): array|null
    {
        $wizardStepsData = $this->getFromWizardSession($this->getStepsSessionName('all'));

        if (!empty($wizardStepsData) && is_array($wizardStepsData)){
            if ($strict) {
                return $wizardStepsData[$key] ?? null;
            }
            return $wizardStepsData[$key] ?? $wizardStepsData;
        }

        return  null;
    }

    public function getWizardStepMenu($step): mixed
    {
        $menu = $this->getWizardMenu();

        return $menu[$step] ?? [];
    }

    public function getCurrentWizardMenu()
    {
        return $this->getWizardStepMenu($this->getCurrentWizardStep());
    }

    public function getWizardMenuCount(): int
    {
        return count($this->getWizardMenu());
    }

    public function setWizardStepStatus($value, $stepId, int $addStepValue = 0)
    {

        if (($addStepValue >= 1) && is_int($value)) {
            $stepId = $stepId + $addStepValue;
        }

        return $this->setCompletedWizardStep($stepId, $value);
    }

    public function wizardStepIsCurrent($step): bool|string
    {
        if ($this->getCurrentWizardStep() == $step) {
            return true;
        }
        return false;
    }

    public function wizardStepIsCompleted($step): bool
    {
        $data = $this->getCompletedWizardStep();
        return isset($data[$step]) && $data[$step] == true ? true : false;
    }

    public function previousWizardStep()
    {
        $step = $this->getCurrentWizardStep();
        $goTo = 0;

        if ($step == 1) {
            $goTo = 1;
        }elseif (($step > 1) && ($step <= $this->getMaxWizardSteps())){
            $goTo = $step - 1;
        }else{
            $goTo = $step - 1;
        }

        // store current step
        $this->setCurrentWizardStep($goTo);

        return $this;
    }

    public function nextWizardStep()
    {
        $rules = $this->setWizardStepsData();
        $step  =$this->getCurrentWizardStep() ?? $this->defaultWizardStep;

        if (in_array($step, array_keys($rules))) {

            $rules = $rules[$step];
            if (method_exists($this, 'getUpdatedWizardValidationRules'))
            {
                if (is_array($this->getUpdatedWizardValidationRules()) && count($this->getUpdatedWizardValidationRules()) >= 1)
                {
                    $rules = array_merge($this->getUpdatedWizardValidationRules(), $rules); //@todo
                }
            }

            $this->setWizardValidationRules($rules);

            if (!empty($rules))
            {
                $this->validate($rules);
            }

        }

        // register step as done
        $this->setCompletedWizardStep($step, true);

        // proceed
        $this->setCurrentWizardStep($step + 1);
    }

    public function changeWizardStep(int $step)
    {
        if ($this->wizardStepIsCompleted($step)) {
            $this->setCurrentWizardStep($step);
        }
    }

    public function canGoBackOnWizardStep(): bool
    {
        return ($this->getCurrentWizardStep() >= 2) && ($this->getCurrentWizardStep() <= $this->getMaxWizardSteps());
    }

    public function canGoForwardOnWizardStep(): bool
    {
        return ($this->getCurrentWizardStep() > 1) && ($this->getCurrentWizardStep() < $this->getMaxWizardSteps());
    }

    public function canSubmitWizard(): bool
    {
        return $this->getCurrentWizardStep() == ($this->getMaxWizardSteps());
    }

    public function wizardStepStatus(string|int $step): string
    {
        if($this->wizardStepIsCompleted($step)) {
            return 'completed';
        } elseif($this->wizardStepIsCurrent($step)) {
            return 'current';
        } elseif(!$this->wizardStepIsCompleted($step) && !$this->wizardStepIsCurrent($step)) {
            return 'pending';
        } else{
            return 'disabled';
        }
    }

    protected function setWizardValidationRules($rule)
    {
        $previousRules = $this->getWizardValidationRules() ?? [];
        $currentRules  = array_merge($previousRules, $rule);

        $this->addToWizardSession('rules', $currentRules);
    }

    public function getWizardValidationRules(?string $key = null)
    {
        return $this->getFromWizardSession('rules');
    }

    /*
    |--------------------------------------------------------------------------
    | Session Configuration
    |--------------------------------------------------------------------------
    |
    |
    */

    public function addToWizardSession(string $key, mixed $value, bool $push = false)
    {
        if ($push){
            session()->push("{$this->wizardSessionName}.$key", $value);
        }else{
            session()->put("{$this->wizardSessionName}.$key", $value);
        }
    }

    public function getFromWizardSession(?string $key)
    {
        if (!empty($key))
        {
            return session()->get($this->wizardSessionName . ".$key");
        }
        return session()->get($this->wizardSessionName);
    }

    public function resetWizard()
    {
        session()->forget($this->wizardSessionName);
    }

    /*
    |--------------------------------------------------------------------------
    | Wizard Progress Percentile
    |--------------------------------------------------------------------------
    |
    |
    */

    public function progressbarPercentile(): float|int
    {

        $completed  = [];

        if (is_array($this->getCompletedWizardStep()) && !empty($this->getCompletedWizardStep()))
        {
            foreach ($this->getCompletedWizardStep() as $step => $status)
            {
                if ($status)
                {
                    $completed[] = $step;
                }
            }
        }

        $percentage = (count($completed) / $this->getWizardMenuCount()) * 100;

        return floor($percentage);
    }

}
