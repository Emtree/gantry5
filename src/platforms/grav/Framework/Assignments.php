<?php
/**
 * @package   Gantry5
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2015 RocketTheme, LLC
 * @license   MIT
 *
 * http://opensource.org/licenses/MIT
 */

namespace Gantry\Framework;

use Gantry\Component\Gantry\GantryTrait;
use RocketTheme\Toolbox\File\YamlFile;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;

class Assignments
{
    protected $configuration;
    protected $className = '\Gantry\%s\Assignments\Assignments%s';
    protected $platform = 'Grav';

    public function __construct($configuration)
    {
        $this->configuration = $configuration;
    }

    public function get()
    {
        return $this->getTypes();
    }

    public function set(array $data)
    {
        $this->save($data);
    }

    /**
     * Save assignments for the configuration.
     *
     * @param array $data
     */
    public function save(array $data)
    {
        $data = $data['assignments'];
        foreach ($data as $tname => &$type) {
            foreach ($type as $gname => &$group) {
                foreach ($group as $key => $value) {
                    if (!$value) {
                        unset($group[$key]);
                    } else {
                        $group[$key] = (bool) $value;
                    }
                }
                if (empty($group)) {
                    unset($type[$gname]);
                }
            }
            if (empty($type)) {
                unset($data[$tname]);
            }
        }

        $gantry = Gantry::instance();

        /** @var UniformResourceLocator $locator */
        $locator = $gantry['locator'];

        // Save layout into custom directory for the current theme.
        $save_dir = $locator->findResource("gantry-config://{$this->configuration}", true, true);
        $filename = "{$save_dir}/assignments.yaml";

        $file = YamlFile::instance($filename);
        $file->save($data);
        $file->free();
    }

    public function types()
    {
        return ['page'];
    }

    public function getTypes()
    {
        $list = [];

        foreach($this->types() as $type) {
            $class = sprintf($this->className, $this->platform, ucfirst($type));

            if (!class_exists($class)) {
                throw new \RuntimeException("Assignment type '{$type}' is missing");
            }

            $instance = new $class;
            $list[$type] = $instance->listRules();
            unset($instance);
        }

        return $list;
    }

    public function getAssignment()
    {
        return 'default';
    }

    public function setAssignment($value)
    {
        throw new \RuntimeException('Not implemented');
    }

    public function assignmentOptions()
    {
        return [];
    }
}