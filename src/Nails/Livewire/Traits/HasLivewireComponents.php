<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Livewire\Traits;

use Livewire\Livewire;

trait HasLivewireComponents
{

    private ?string $livewireComponentAlias = null;
    private ?array  $livewireComponents     = null;

    /**
     * @return string|null
     */
    public function getLivewireComponentAlias(): ?string
    {
        return $this->livewireComponentAlias;
    }

    /**
     * @param string|null $livewireComponentAlias
     * @return self
     */
    public function setLivewireComponentAlias(?string $livewireComponentAlias): self
    {
        $this->livewireComponentAlias = $livewireComponentAlias;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getLivewireComponents(): ?array
    {
        return $this->livewireComponents;
    }

    /**
     * @param string $alias
     * @param mixed $component
     * @return self
     */
    public function setLivewireComponents(string $alias, $component): self
    {
        $alias = trim($alias);
        if (is_array($this->livewireComponents) && (count($this->livewireComponents) > 0)) {
            $this->livewireComponents = array_merge($this->livewireComponents, [
                $alias => $component,
            ]);
        } else {
            $this->livewireComponents = [
                $alias => $component,
            ];
        }
        return $this;
    }

    public function registerLivewireComponents()
    {

        $payloads = $this->livewireComponents;
        if (is_array($payloads) && (count($payloads)) > 0) {
            foreach ($payloads as $alias => $components) {
                $alias = trim($alias);
                if (is_array($components) && (count($components) > 0)) {
                    foreach ($components as $component) {
                        Livewire::component($alias, $component);
                    }
                } else {
                    Livewire::component($alias, $components);
                }
            }
        }

    }

}
