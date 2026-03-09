# config files 

```php
$dotenv = new Symfony\Component\Dotenv\Dotenv();
$dotenv->load(__DIR__ . '/.env');
```

# gemini nano banana example
```php

$ai = Axl::build(GeminiProvider::class)
    ->apiKey($_ENV['gemini_api_key'])
    ->use('nano_banana')
    ->make();

try {
    $resp = $ai->complete(new ImageContext(
        Messages::system(new Text("You are a professional web artist")),
        Messages::user(
            new Text("Generate a social media subscribe button that's fairly close to the original youtube subscribe button"),
        )),
    );

    $img = base64_decode($resp->base64());
    if($i = fopen('nanobanana.png', 'w+')) {
        fwrite($i, $img, strlen($img));
        fclose($i);
    }

    echo "Completed generation";
} catch(\Exception $e) {
    if($f = fopen(__DIR__ . '/debug.log', 'w+')) {
        fwrite($f, $e->getMessage(). $e->getTraceAsString(), strlen($e->getMessage(). $e->getTraceAsString()));
        fclose($f);
    }
}
```

# claude
```php
$ai = Axl::build(AnthropicProvider::class)
    ->apiKey($_ENV['claude_api_key'])
    ->use('opus45', 'claude-opus-4-6')
    ->options(['max_tokens' => 1500])
    ->make();

$resp = $ai->complete(new TextContext(
    Messages::system(new Text("You are a masterful comedian like dave chappelle")),
    Messages::user(
        new Text("Make fun of this train image"),
        new ImageUrl("https://i.ytimg.com/vi/BSchdxFHlT4/hq720.jpg"),
    )),
);

echo $resp->text();
//print_r($resp->rawJsonArray());
```

## Stream example

```php
$ai = Axl::build(AnthropicProvider::class)
    ->apiKey($_ENV['claude_api_key'])
    ->use('opus45', 'claude-opus-4-6') // force a different model w/ same settings w/o recreation or setup
    ->options(['stream' => true, 'max_tokens' => 5000])
    ->make();

$stream = $ai->stream(new TextContext(
    Messages::system(new Text("You are a file analyzer")),
    Messages::user(
        new Text("Describe this file"),
        new FileToBase64(__DIR__ . '/document-example.pdf', ['filename' => 'document-example.pdf', ])
    )),
);

// uses generators
foreach($stream as $resp) {
    echo $resp;
}

echo PHP_EOL . PHP_EOL;
echo "script completed" . PHP_EOL;
```