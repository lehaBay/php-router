<?php
/**
 * Created by Alexey Fomin
 * Email: fominleha@gmail.com
 * Date: 23.12.17 15:58
 * Licensed under the MIT license
 */

namespace Fastero\Router\PathHandler;


interface GeneratorInterface
{
    public function setOptions(array $options);

    public function makePath(array $urlParameters): string;

}