<?php
namespace AxlCore\Models;

use AxlCore\Contracts\ModelTypeInterface;

class ModelType implements ModelTypeInterface
{
    const string CHAT = 'chat';
    const string FILE = 'file';
    const string AUDIO = 'audio';
}