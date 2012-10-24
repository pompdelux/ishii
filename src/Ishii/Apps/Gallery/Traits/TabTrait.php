<?php

namespace Ishii\Apps\Gallery\Traits;

trait TabTrait
{
    public function tab(Request $request)
    {
        return $this->app->render("Gallery/tab/index.twig");
    }
}
