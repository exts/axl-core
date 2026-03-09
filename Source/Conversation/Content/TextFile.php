<?php
namespace AxlCore\Conversation\Content;

class TextFile extends Content
{
    protected ContentType $type = ContentType::textFile;

    #[\Override]
    public function data() : string
    {
        if(!file_exists($this->data)) {
            throw new \Exception("The TextFile: $this->data doesn't exist");
        }

        $content = file_get_contents($this->data);

        return sprintf("%sAttached Text File Json Object:\n%s", '',
            json_encode([
                'file_name' => $this->metadata('filename'),
                'file_size' => $this->formatBytes(strlen($content)),
                'file_content' => $content,
                'file_mimetype' => $this->metadata('mimetype'),
            ])
        );
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

}