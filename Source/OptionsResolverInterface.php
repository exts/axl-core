<?php
namespace AxlCore;

interface OptionsResolverInterface
{
    public function normalize(array $options) : array;
}