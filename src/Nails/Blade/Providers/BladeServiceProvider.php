<?php declare(strict_types=1);

namespace Simtabi\Laranail\Nails\Blade\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Simtabi\Laranail\Nails\Blade\Supports\DirectiveParser;

class BladeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerDirectives();
    }


    /**
     * Register all directives.
     *
     * @return void
     */
    public function registerDirectives()
    {
        collect($this->directives())->each(function ($item, $key) {
            Blade::directive($key, $item);
        });
    }


    private function directives(): array
    {
        return [

            /*
            |---------------------------------------------------------------------
            | @istrue / @isfalse
            |---------------------------------------------------------------------
            |
            | These directives can be used in different ways.
            | @istrue($v) Echo this @endistrue, @istrue($v, 'Echo this')
            | or @istrue($variable, $echoThisVariables)
            |
            */

            'istrue' => function ($expression) {
                if (Str::contains($expression, ',')) {
                    $expression = DirectiveParser::multipleArgs($expression);

                    return implode('', [
                        "<?php if (isset({$expression->get(0)}) && (bool) {$expression->get(0)} === true) : ?>",
                        "<?php echo {$expression->get(1)}; ?>",
                        '<?php endif; ?>',
                    ]);
                }

                return "<?php if (isset({$expression}) && (bool) {$expression} === true) : ?>";
            },

            'endistrue' => function ($expression) {
                return '<?php endif; ?>';
            },

            'isfalse' => function ($expression) {
                if (Str::contains($expression, ',')) {
                    $expression = DirectiveParser::multipleArgs($expression);

                    return implode('', [
                        "<?php if (isset({$expression->get(0)}) && (bool) {$expression->get(0)} === false) : ?>",
                        "<?php echo {$expression->get(1)}; ?>",
                        '<?php endif; ?>',
                    ]);
                }

                return "<?php if (isset({$expression}) && (bool) {$expression} === false) : ?>";
            },

            'endisfalse' => function ($expression) {
                return '<?php endif; ?>';
            },

            /*
            |---------------------------------------------------------------------
            | @isnull / @isnotnull
            |---------------------------------------------------------------------
            |
            | These directives can be used in different ways.
            | @isnull($v) Echo this @endisnull, @isnull($v, 'Echo this')
            | or @isnull($variable, $echoThisVariables)
            |
            */

            'isnull' => function ($expression) {
                if (Str::contains($expression, ',')) {
                    $expression = DirectiveParser::multipleArgs($expression);

                    return implode('', [
                        "<?php if (is_null({$expression->get(0)})) : ?>",
                        "<?php echo {$expression->get(1)}; ?>",
                        '<?php endif; ?>',
                    ]);
                }

                return "<?php if (is_null({$expression})) : ?>";
            },

            'endisnull' => function ($expression) {
                return '<?php endif; ?>';
            },

            'isnotnull' => function ($expression) {
                if (Str::contains($expression, ',')) {
                    $expression = DirectiveParser::multipleArgs($expression);

                    return implode('', [
                        "<?php if (! is_null({$expression->get(0)})) : ?>",
                        "<?php echo {$expression->get(1)}; ?>",
                        '<?php endif; ?>',
                    ]);
                }

                return "<?php if (! is_null({$expression})) : ?>";
            },

            'endisnotnull' => function ($expression) {
                return '<?php endif; ?>';
            },

            /*
            |---------------------------------------------------------------------
            | @mix
            |---------------------------------------------------------------------
            |
            | Usage: @mix('js/app.js') of @mix('css/app.css')
            |
            */

            'mix' => function ($expression) {
                if (Str::endsWith($expression, ".css'")) {
                    return '<link rel="stylesheet" href="<?php echo mix('.$expression.') ?>">';
                }

                if (Str::endsWith($expression, ".js'")) {
                    return '<script src="<?php echo mix('.$expression.') ?>"></script>';
                }

                return "<?php echo mix({$expression}); ?>";
            },

            /*
            |---------------------------------------------------------------------
            | @style
            |---------------------------------------------------------------------
            |
            | Usage: @style('/css/app.css') or @style body{ color: red; } @endstyle
            |
            */

            'style' => function ($expression) {
                if (! empty($expression)) {
                    return '<link rel="stylesheet" href="'.DirectiveParser::stripQuotes($expression).'">';
                }

                return '<style>';
            },

            'endstyle' => function () {
                return '</style>';
            },

            /*
            |---------------------------------------------------------------------
            | @script
            |---------------------------------------------------------------------
            |
            | Usage: @script('/js/app.js') or @script alert('Message') @endstyle
            |
            */

            'script' => function ($expression) {
                if (! empty($expression)) {
                    return '<script src="'.DirectiveParser::stripQuotes($expression).'"></script>';
                }

                return '<script>';
            },

            'endscript' => function () {
                return '</script>';
            },

            /*
            |---------------------------------------------------------------------
            | @window
            |---------------------------------------------------------------------
            |
            | This directive can be used to add variables to javascript's window
            | Usage: @window('name', ['key' => 'value'])
            |
            */

            'window' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                $variable = DirectiveParser::stripQuotes($expression->get(0));

                return  implode("\n", [
                    '<script>',
                    "window.{$variable} = <?php echo is_array({$expression->get(1)}) ? json_encode({$expression->get(1)}) : {$expression->get(1)}; ?>;",
                    '</script>',
                ]);
            },

            /*
            |---------------------------------------------------------------------
            | @inline
            |---------------------------------------------------------------------
            */

            'inline' => function ($expression) {
                $include = implode("\n", [
                    "/* {$expression} */",
                    "<?php include public_path({$expression}) ?>\n",
                ]);

                if (Str::endsWith($expression, ".html'")) {
                    return $include;
                }

                if (Str::endsWith($expression, ".css'")) {
                    return "<style>\n".$include.'</style>';
                }

                if (Str::endsWith($expression, ".js'")) {
                    return "<script>\n".$include.'</script>';
                }
            },

            /*
            |---------------------------------------------------------------------
            | @routeis
            |---------------------------------------------------------------------
            */

            'routeis' => function ($expression) {
                return "<?php if (fnmatch({$expression}, Route::currentRouteName())) : ?>";
            },

            'endrouteis' => function ($expression) {
                return '<?php endif; ?>';
            },

            'routeisnot' => function ($expression) {
                return "<?php if (! fnmatch({$expression}, Route::currentRouteName())) : ?>";
            },

            'endrouteisnot' => function ($expression) {
                return '<?php endif; ?>';
            },

            /*
            |---------------------------------------------------------------------
            | @instanceof
            |---------------------------------------------------------------------
            */

            'instanceof' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return  "<?php if ({$expression->get(0)} instanceof {$expression->get(1)}) : ?>";
            },

            'endinstanceof' => function () {
                return '<?php endif; ?>';
            },

            /*
            |---------------------------------------------------------------------
            | @typeof
            |---------------------------------------------------------------------
            */

            'typeof' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return  "<?php if (gettype({$expression->get(0)}) == {$expression->get(1)}) : ?>";
            },

            'endtypeof' => function () {
                return '<?php endif; ?>';
            },

            /*
            |---------------------------------------------------------------------
            | @dump, @dd
            |---------------------------------------------------------------------
            */

            'dump' => function ($expression) {
                return "<?php dump({$expression}); ?>";
            },

            'dd' => function ($expression) {
                return "<?php dd({$expression}); ?>";
            },

            /*
            |---------------------------------------------------------------------
            | @pushonce
            |---------------------------------------------------------------------
            */

            'pushonce' => function ($expression) {
                [$pushName, $pushSub] = explode(':', trim(substr($expression, 1, -1)));

                $key = '__pushonce_'.str_replace('-', '_', $pushName).'_'.str_replace('-', '_', $pushSub);

                return "<?php if(! isset(\$__env->{$key})): \$__env->{$key} = 1; \$__env->startPush('{$pushName}'); ?>";
            },

            'endpushonce' => function () {
                return '<?php $__env->stopPush(); endif; ?>';
            },

            /*
            |---------------------------------------------------------------------
            | @repeat
            |---------------------------------------------------------------------
            */

            'repeat' => function ($expression) {
                return "<?php for (\$iteration = 0 ; \$iteration < (int) {$expression}; \$iteration++): ?>";
            },

            'endrepeat' => function ($expression) {
                return '<?php endfor; ?>';
            },

            /*
             |---------------------------------------------------------------------
             | @data
             |---------------------------------------------------------------------
             */

            'dataAttributes' => function ($expression) {
                $output = 'collect((array) '.$expression.')
            ->map(function($value, $key) {
                return "data-{$key}=\"{$value}\"";
            })
            ->implode(" ")';

                return "<?php echo $output; ?>";
            },

            /*
            |---------------------------------------------------------------------
            | @fa, @fas, @far, @fal, @fab, @mdi, @glyph
            |---------------------------------------------------------------------
            */

            'fa' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return '<i class="fa fa-'.DirectiveParser::stripQuotes($expression->get(0)).' '.DirectiveParser::stripQuotes($expression->get(1)).'"></i>';
            },

            'fad' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return '<i class="fad fa-'.DirectiveParser::stripQuotes($expression->get(0)).' '.DirectiveParser::stripQuotes($expression->get(1)).'"></i>';
            },

            'fas' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return '<i class="fas fa-'.DirectiveParser::stripQuotes($expression->get(0)).' '.DirectiveParser::stripQuotes($expression->get(1)).'"></i>';
            },

            'far' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return '<i class="far fa-'.DirectiveParser::stripQuotes($expression->get(0)).' '.DirectiveParser::stripQuotes($expression->get(1)).'"></i>';
            },

            'fal' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return '<i class="fal fa-'.DirectiveParser::stripQuotes($expression->get(0)).' '.DirectiveParser::stripQuotes($expression->get(1)).'"></i>';
            },

            'fab' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return '<i class="fab fa-'.DirectiveParser::stripQuotes($expression->get(0)).' '.DirectiveParser::stripQuotes($expression->get(1)).'"></i>';
            },

            'mdi' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return '<i class="mdi mdi-'.DirectiveParser::stripQuotes($expression->get(0)).' '.DirectiveParser::stripQuotes($expression->get(1)).'"></i>';
            },

            'glyph' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return '<i class="glyphicons glyphicons-'.DirectiveParser::stripQuotes($expression->get(0)).' '.DirectiveParser::stripQuotes($expression->get(1)).'"></i>';
            },

            'bi' => function ($expression) {
                $expression = DirectiveParser::multipleArgs($expression);

                return '<i class="bi bi-'.DirectiveParser::stripQuotes($expression->get(0)).' '.DirectiveParser::stripQuotes($expression->get(1)).'"></i>';
            },

            /*
            |---------------------------------------------------------------------
            | @haserror
            |---------------------------------------------------------------------
            */

            'haserror' => function ($expression) {
                return '<?php if (isset($errors) && $errors->has('.$expression.')): ?>';
            },

            'endhaserror' => function () {
                return '<?php endif; ?>';
            },

            /*
            |---------------------------------------------------------------------
            | @pushonce
            |---------------------------------------------------------------------
            */

            'pushonce' => function ($expression) {
                $domain      = explode(':', trim(substr($expression, 2, -2)));
                $push_name   = $domain[0];
                $push_sub    = $domain[1];
                $isDisplayed = '__pushonce_'.$push_name.'_'.$push_sub;
                return "<?php if(!isset(\$__env->{$isDisplayed})): \$__env->{$isDisplayed} = true; \$__env->startPush('{$push_name}'); ?>";
            },

            'endpushonce' => function () {
                return '<?php $__env->stopPush(); endif; ?>';
            },

            /*
            |---------------------------------------------------------------------
            | @base64Image
            |---------------------------------------------------------------------
            */

            'base64Image' => function ($expression) {
                return "<?php echo 'data:image/' . pathinfo($expression, PATHINFO_EXTENSION) . ';base64,' . base64_encode(file_get_contents($expression)); ?>";
            },

            /*
            |---------------------------------------------------------------------
            | @javascript
            |---------------------------------------------------------------------
            */

            'javascript' => function ($expression) {
                $expression = $this->makeBackwardsCompatible($expression);

                return "<?= app('\Simtabi\Laranail\Nails\Blade\Supports\DirectiveRenderer')->render{$expression}; ?>";
            },

        ];
    }

    public function makeBackwardsCompatible($expression): string
    {
        return "({$expression})";
    }
}
