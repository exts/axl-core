<?php
namespace AxlCore\Request;

use function array_keys;

class HeaderContainer
{
    protected bool $option_append_merge = false;
    protected bool $option_unique_headers = false;

    protected array $all_headers = [];

    public function __construct(
        protected array $headers = [],
    ){
    }

    public function addHeaders(array $headers, array $options = [
        'append_merge' => false,
        'unique_headers' => false,
    ]) : void
    {
        $option_append_merge = boolval($options['append_merge'] ?? $this->option_append_merge);
        $option_unique_headers = boolval($options['unique_headers'] ?? $this->option_unique_headers);

        if($option_append_merge || !$option_unique_headers) {
            $this->headers += $headers;
        } else {
            foreach($headers as $key => $val) {
                if(in_array($key, array_keys($this->headers))) continue;
                $this->headers[$key] = $val;
            }
        }
    }

    public function getHeaders() : array
    {
        return $this->headers;
    }
}