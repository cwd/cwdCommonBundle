<?php
/*
 * This file is part of CwdCommonBundle
 *
 * (c)2016 cwd.at GmbH <office@cwd.at>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Cwd\CommonBundle\Service;

use Cwd\CommonBundle\Manager\AbstractManager;

/**
 * Base Service class to ease creation of model-specific service classes.
 * If this code proves useful, we should consider moving it into the Generic service.
 *
 * @package Cwd\CommonBundle\Service
 * @author  Ludwig Ruderstaller <lr@cwd.at>
 * @deprecated Use Cwd\CommonBundle\Manager\AbstractManager instead!
 */
abstract class AbstractBaseService extends AbstractManager
{
}
