<?php
namespace AxlCore\Request;

use AxlCore\Contracts\ContentInterface;
use AxlCore\Contracts\ContextInterface;
use AxlCore\Contracts\MessageObjectInterface;
use AxlCore\Contracts\Models\ChatModelInterface;
use AxlCore\Contracts\Models\ModelInterface;
use AxlCore\Contracts\ProviderInterface;
use AxlCore\Conversation\Content\ContentType;
use AxlCore\Conversation\Content\Text;
use AxlCore\Conversation\Messages\MessageType;
use AxlCore\Exceptions\Request\PayloadException;
use AxlCore\Exceptions\Request\PayloadOptionException;
use AxlCore\OptionsResolver;

class PayloadBuilder
{
    public function __construct(
        protected ProviderInterface $provider
    ){
    }

    /**
     * @param ContextInterface $context
     * @return array
     * @throws PayloadException
     * @throws PayloadOptionException
     */
    public function buildPayload(ContextInterface $context) : array
    {
        $model = $this->getProviderModel();

        /** @var MessageObjectInterface[] $messages_block */
        $messages_block = $context->getMessages();

        if(empty($messages_block)) {
            throw new PayloadException("Provide a message content to this context before making a request");
        }

        if($model instanceof ChatModelInterface && $model->isFlattenPromptEnabled()) {
            return $this->flattenPayload($model, $messages_block);
        }

        $messages = [];
        $system_messages = [];
        foreach($messages_block as $message) {
            // handles system messages differently when enabled
            if($model->extractSystemMessages()
                && $message->type() === MessageType::system) {
                $system_messages[] = $message; continue;
            }
            $messages[] = $this->buildMessage($message);
        }

        // Build final payload
        $payload = [
            'model' => $this->getModelName(),
            $this->getMessagesKey() => $messages,
        ];

        if(!empty($system_messages)) {
            $payload[$model->mapRole('system')] = $model->buildSystemPayload($system_messages);
        }

        $options = (new OptionsResolver(
            $model->getMappedOptions(), $model->getAcceptedOptions()))->normalize($this->provider->getOptions());

        $payload = array_merge($payload, $options);

        // validate required setOptions
        $this->validateOptions($payload);

        return $payload;
    }

    /**
     * @param MessageObjectInterface $message
     * @return array
     * @throws PayloadException
     */
    protected function buildMessage(MessageObjectInterface $message) : array
    {
        $model = $this->provider->getModelOrThrow();

        $role = $model->mapRole($message->type()->name);
        $content_key = $model->contentKey();
        $content_items = $message->content();  // Get content objects (Text, Image, etc.)

        if(empty($content_items)) {
            throw new PayloadException("Context block can't have empty messages");
        }

        // merge multiple text content's into a single text content
        $total = 0;
        /** @var ContentInterface $item */
        foreach ($content_items as $item) {
            if($item->type() !== ContentType::text) continue;
            ++$total;
        }

        if($total === count($content_items)) {
            $str = [];
            /** @var ContentInterface $item */
            foreach ($content_items as $item) {
                $str[] = $item->data();
            }

            $content_items = [new Text(implode("\n\n", $str))];
        }

        // Multiple items or non-text? Use array format
        $content_blocks = [];
        /** @var ContentInterface $item */
        foreach ($content_items as $item) {
            $template = $model->getContentTemplate($item->type());

            // Pass the value (or in the future entire object for complex types like Document)
            $content_blocks[] = $template($item);
        }

        return [
            'role' => $role,
            $content_key => $content_blocks,
        ];
    }

    protected function flattenPayload(ModelInterface $model, array $messages_block) : array
    {
        $flattened_prompt = [];
        foreach($messages_block as $message_object) {

            if(empty($message_object->content())) {
                throw new PayloadException("Context block can't have empty messages");
            }

            /** @var ContentInterface $content */
            foreach($message_object->content() as $content) {
                $template = $model->getContentTemplateFlattened($content->type());
                $flattened_prompt[] = !empty($template) ? $template($content) : $content->data();
            }
        }

        $payload = [
            'model' => $this->getModelName(),
            $this->getMessagesKey() => implode("\n\n", $flattened_prompt),
        ];

        $options = (new OptionsResolver(
            $model->getMappedOptions(), $model->getAcceptedOptions()))->normalize($this->provider->getOptions());

        $payload = array_merge($payload, $options);

        // validate required setOptions
        $this->validateOptions($payload);

        return $payload;
    }

    /**
     * @param array $payload
     * @return void
     * @throws PayloadOptionException
     */
    protected function validateOptions(array $payload) : void
    {
        // validate required setOptions
        $not_found = [];
        $required_options = $this->provider->endpointOptionsRequired();
        foreach($required_options as $required_option) {
            if(!in_array($required_option, array_keys($payload))) {
                $not_found[] = $required_option;
            }
        }

        if(!empty($not_found)) {
            throw new PayloadOptionException("Required option(s) missing: " . implode(", ", $not_found));
        }
    }

    /**
     * @return string
     */
    protected function getMessagesKey() : string
    {
        $model = $this->provider->getModelOrThrow();

        return $model->messagesKey();
    }

    /**
     * @return array
     */
    protected function getHeaders() : array
    {
        return $this->provider->getHeaders();
    }

    /**
     * @return ModelInterface
     */
    protected function getProviderModel() : ModelInterface
    {
        return $this->provider->getModelOrThrow();
    }

    /**
     * @return string
     */
    protected function getModelName() : string
    {
        return $this->getProviderModel()->getModelVersion();
    }
}