<?php

namespace App\Controller;

use App\Kernel;
use App\Model\ApiResponse\ApiResponse;
use App\Utils\ContainerAdapter;
use App\Utils\Response\Errors;
use Doctrine\ODM\MongoDB\DocumentManager;
use GraphQL\GraphQL;
use GraphQL\Type\Schema;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class GraphQLController
 * @package App\Controller
 * @Route(defaults={"_format"="json"})
 */
class GraphQLController extends AbstractController
{
    /**
     * @var Kernel
     */
    private Kernel $kernel;

    /**
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * @var DocumentManager
     */
    private DocumentManager $documentManager;

    public function __construct(Kernel $kernel, RequestStack $requestStack, DocumentManager $documentManager)
    {
        $this->kernel = $kernel;
        $this->requestStack = $requestStack;
        $this->documentManager = $documentManager;
    }

    /**
     * @Route("/graphql", methods={"POST"}, name="GRAPHQL")
     */
    public function graphQL(): Response
    {
        dd();
        $input = json_decode($this->requestStack->getMasterRequest()->getContent(), true);
        $response = (new ApiResponse());
        $schema = require $this->kernel->getProjectDir() . '/src/GraphQL/schema.php';
        $schema = new Schema($schema);
        if (!isset($input['query'])) {
            $response->addError(Errors::GRAPHQL_QUERY_MISSING_FIELD);
            return $response->getResponse();
        }
        $query = $input['query'];
        $variableValues = isset($input['variables']) ? $input['variables'] : NULL;
        $containerAdapter = new ContainerAdapter();
        $containerAdapter->documentManager = $this->documentManager;
        $result = GraphQL::executeQuery(
            $schema,
            $query,
            $containerAdapter,
            NULL, // context set to null
            $variableValues
        );

        if ($result->errors) {
            $code = 400;
            // catch others error than 400 (like 403)
            if (
                $result->errors[0]->getPrevious() != NULL
                && $result->errors[0]->getPrevious()->getCode() != 0
                && $result->errors[0]->getPrevious()->getCode() > 400
                && $result->errors[0]->getPrevious()->getCode() < 550
            ) {
                $code = $result->errors[0]->getPrevious()->getCode();
            }
            $response->setCustomErrors($result->errors);
            $response->setCode($code);
        }
        return $response->getResponse();
    }
}
