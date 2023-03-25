<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Seeder\Services;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Simtabi\Laranail\Nails\Console\ConsoleProgressBar;
use Simtabi\Laranail\Nails\Seeder\Events\FinishedSeederEvent;
use Simtabi\Laranail\Nails\Seeder\Events\StartingSeederEvent;
use Simtabi\Laranail\Nails\Seeder\Traits\HasDatabaseSeeder;

class SeedManager extends Seeder
{

    use HasDatabaseSeeder;

    private array                $parameters = [];
    private bool                 $silent     = false;
    protected array              $errors     = [];
    private array|string|null    $seeds      = null;
    protected ConsoleProgressBar $progressBar;

    public function __construct(array|string|null $seeds = null, array $parameters = [], bool $silent = false)
    {

        $this->parameters = $parameters;
        $this->silent     = $silent;

        if (is_string($seeds)) {
            $this->seeds = explode(",", $seeds);
        }

        $this->progressBar = new ConsoleProgressBar();
    }

    public function loadSeederFiles(int $total, string $folder, string $fileName, bool $fromDatabasePath = true): mixed
    {

        $images = [];
        for ($i = 1; $i <= $total; $i++) {

            if($fromDatabasePath) {
                $path = resource_path("seeders/$folder/$fileName");
            } else {
                $path = database_path("seeders/files/$folder/$fileName");
            }

            if (File::exists($path)) {
                $images[] = "$folder/$fileName";
            }
        }

        if (count($images) < 1) {
            return null;
        }

        return $total == 1 ? $images[0] : $images;
    }

    public function run(): void
    {
        if (!empty($this->seeds)) {
            $this->fireStartedEvent();

            foreach ($this->seeds as $seed) {
                $this->call($seed, $this->silent, $this->parameters);
            }

            $this->fireFinishedEvent();
        }
    }

    public function fireStartedEvent(): void
    {
        event(new StartingSeederEvent());
    }

    public function fireFinishedEvent(): void
    {
        event(new FinishedSeederEvent());
    }

    public function setErrors(array|string $errors): static
    {
        $errors = ! is_array($errors) ? [$errors] : $errors;

        if (count($this->errors) >= 1) {
            $this->errors = array_merge($this->errors, $errors);
        } else {
            $this->errors = $errors;
        }

        return $this;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

}
