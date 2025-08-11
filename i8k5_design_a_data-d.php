<?php

class DataDrivenChatbotController {
    private $intents;
    private $entities;
    private $responses;

    function __construct($data) {
        $this->intents = $data['intents'];
        $this->entities = $data['entities'];
        $this->responses = $data['responses'];
    }

    public function processMessage($message) {
        $intent = $this->identifyIntent($message);
        $entities = $this->extractEntities($message, $intent);
        $response = $this->getResponse($intent, $entities);

        return $response;
    }

    private function identifyIntent($message) {
        foreach ($this->intents as $intent) {
            if (preg_match($intent['pattern'], $message)) {
                return $intent['name'];
            }
        }

        return 'unknown';
    }

    private function extractEntities($message, $intent) {
        $entities = [];

        foreach ($this->entities as $entity) {
            if ($entity['intent'] == $intent) {
                preg_match_all($entity['pattern'], $message, $matches);
                $entities[$entity['name']] = $matches[0];
            }
        }

        return $entities;
    }

    private function getResponse($intent, $entities) {
        foreach ($this->responses as $response) {
            if ($response['intent'] == $intent) {
                $responseText = $response['text'];
                foreach ($entities as $entityName => $entityValue) {
                    $responseText = str_replace('{' . $entityName . '}', $entityValue[0], $responseText);
                }
                return $responseText;
            }
        }

        return 'Sorry, I didn\'t understand that.';
    }
}

$data = [
    'intents' => [
        ['name' => 'greeting', 'pattern' => '/(hello|hi)/i'],
        ['name' => 'goodbye', 'pattern' => '/(bye|see you)/i'],
    ],
    'entities' => [
        ['name' => 'name', 'intent' => 'greeting', 'pattern' => '/\b(\w+)\b/i'],
        ['name' => 'location', 'intent' => 'goodbye', 'pattern' => '/\b(in|at|from) (\w+)/i'],
    ],
    'responses' => [
        ['intent' => 'greeting', 'text' => 'Hello, {name}!'],
        ['intent' => 'goodbye', 'text' => 'Goodbye from {location}!'],
    ],
];

$controller = new DataDrivenChatbotController($data);

$message = 'Hello, my name is John.';
echo $controller->processMessage($message); // Output: Hello, John!

$message = 'I\'m leaving from New York.';
echo $controller->processMessage($message); // Output: Goodbye from New York!