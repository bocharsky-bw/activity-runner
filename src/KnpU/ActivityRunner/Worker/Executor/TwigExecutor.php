<?php

namespace KnpU\ActivityRunner\Worker\Executor;
use KnpU\ActivityRunner\ErrorHandler\TwigErrorHandler;
use KnpU\ActivityRunner\Exception\TwigException;

/**
 * This is actually used inside the user-executed PHP code that runs Twig
 *
 * See twig_worker.render.php.twig
 *
 * It prevents me from putting a TON of PHP code in that template.
 */
class TwigExecutor
{
    /**
     * @var \Twig_Environment
     */
    private $environment;

    public function __construct(\Twig_Environment $environment)
    {
        $this->environment = $environment;
    }

    public function executeTwig($templateName, array $variables)
    {
        $executionResult = new ExecutionResult(__DIR__); // the root dir isn't needed here

        $errorHandler = TwigErrorHandler::register();
        try {
            $output = $this->environment->render(
                $templateName,
                $variables
            );
            $executionResult->setOutput($output);
        } catch (TwigException $error) {
            $executionResult->setLanguageError($error->getMessage());
        } catch (\Twig_Error $error) {
            // not doing anything special here... but in the future, we might
            // fetch more information about line numbers, etc
            $executionResult->setLanguageError($error->getMessage());
        } catch (\Exception $error) {
            $executionResult->setLanguageError($error->getMessage());
        } finally {
            $errorHandler->restore();
        }

        return $executionResult;
    }
}