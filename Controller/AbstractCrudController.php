<?php
/*
 * This file is part of CwdCommonBundle
 *
 * (c)2016 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cwd\CommonBundle\Controller;

use Cwd\CommonBundle\Controller\Traits\FancyGridTrait;
use Cwd\CommonBundle\Controller\Traits\HandlerTrait;
use Cwd\CommonBundle\Controller\Traits\LegacyTrait;
use Cwd\FancyGridBundle\Grid\GridBuilderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CrudController
 *
 * @package Cwd\CommonBundle\Controller
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 * @deprecated - Use the individual Traits
 */
abstract class AbstractCrudController extends AbstractBaseController
{
    use FancyGridTrait;
    use LegacyTrait;


}
