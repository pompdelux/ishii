<?php

namespace Ishii\Apps\Gallery\Traits;

trait AppTrait
{
    public function app(Request $request)
    {
        return $this->app->render("Gallery/app/index.twig");
    }
}
