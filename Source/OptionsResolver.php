<?php
namespace AxlCore;

class OptionsResolver implements OptionsResolverInterface
{
    public function __construct(
        protected array $mapped,
        protected array $accepted,
    ){
    }

    public function normalize(array $options) : array
    {
        $normalized = [];
        foreach($options as $key => $value) {
            $curr_key = $this->mapped[$key] ?? $key;
            if(!in_array($curr_key, $this->accepted)) continue;
            $normalized[$curr_key] = $value;
        }

        return $normalized;
    }
}