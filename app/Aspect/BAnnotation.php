<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Aspect;

use Attribute;
use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
#[Attribute(Attribute::TARGET_CLASS)]
class BAnnotation extends AbstractAnnotation
{
    /**
     * @var null|string
     */
    public $prefix = '';

    /**
     * @var string
     */
    public $server = 'http';

    /**
     * @var array
     */
    public $options = [];
}
