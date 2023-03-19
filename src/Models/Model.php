<?php
    namespace Tjall\Magister\Models;

    use Tjall\Magister\Lib;
    use Tjall\Magister\Controllers\Controller;

    abstract class Model {
        public array $data = [];
        protected ?Controller $controller;

        public function __construct(array $magister_data, ?Controller $controller = null) {
            $this->controller = $controller;
            $this->data = $this->parse($magister_data);
        }

        public static function fromMagister(array $magister_data, ?Controller $controller = null): static {
            return new static($magister_data, $controller);
        }

        protected function parse(array $magister_data): array {
            if(method_exists($this, 'beforeMap'))
                call_user_func_array([$this, 'beforeMap'], []);
                
            $data = [];
            
            foreach (static::MAP as $keypath_out => $options) {
                if(isset($this->controller->options['filter']) && 
                   !in_array($keypath_out, $this->controller->options['filter']))
                    continue;
                
                list($keypath_in, $flags) = is_array($options) 
                    ? $options
                    : [ $options, 0];

                $value = null;
                if(is_string($keypath_in)) {
                    $value = Lib::arrayGet($magister_data, $keypath_in);
                }

                if($flags === static::REMAP) {
                    $value = $this->callMethod('remap__'.str_replace('.', '_', $keypath_out), [ $value, $data, $magister_data ]);
                } else if($flags === static::CUSTOM) {
                    $value = $this->callMethod('custom__'.str_replace('.', '_', $keypath_out), [ $value, $data, $magister_data ]);
                }

                if($flags === static::TYPE_DATE) {
                    $value = date('Y-m-d', strtotime($value));
                } else if($flags === static::TYPE_DATETIME) {
                    $value = date(DATE_ATOM, strtotime($value));
                } else if($flags === static::TYPE_DATETIME_OR_NULL && !is_null($value)) {
                    $value = date(DATE_ATOM, strtotime($value));
                } else if($flags === static::TYPE_BOOL) {
                    $value = boolval($value);
                } else if($flags === static::TYPE_STRING) {
                    $value = strval($value);
                } else if($flags === static::TYPE_NUMBER) {
                    $value = floatval($value);
                }

                Lib::arraySet($data, $keypath_out, $value);
            }

            return $data;
        }

        protected function callMethod(string $method, array $args): mixed {
            if(!method_exists($this, $method))
                throw new \Exception("Model '".static::class."' does not have method '".$method."'.");

            return call_user_func_array([$this, $method], $args);
        }

        public function set(string $keypath, $value): void {        
            Lib::arraySet($this->data, $keypath, $value);
        }

        public function get(string $keypath): mixed {
            return Lib::arrayGet($this->data, $keypath);
        }

        public function toArray(): array {
            return $this->data;
        }

        public const REMAP  = 'remap';
        public const CUSTOM = 'custom';

        public const TYPE_DATE             = 'date';
        public const TYPE_BOOL             = 'boolean';
        public const TYPE_DATETIME         = 'datetime';
        public const TYPE_DATETIME_OR_NULL = 'datetimeOrNull';
        public const TYPE_STRING           = 'string';
        public const TYPE_NUMBER           = 'number';

        protected const MAP = [];
    }