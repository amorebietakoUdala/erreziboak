<?php

namespace App\Controller;

use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BaseController extends AbstractController
{
    protected array $queryParams = [];

    protected function loadQueryParameters(Request $request) {
        if (
            $request->getMethod() === Request::METHOD_GET || 
            $request->getMethod() === Request::METHOD_POST || 
            $request->getMethod() === Request::METHOD_DELETE ) {
            $this->queryParams['page'] = 1;
            $this->queryParams['pageSize'] = 10;
            $this->queryParams['sortName'] = 0;
            $this->queryParams['sortOrder'] = 'asc';
            $this->queryParams['returnUrl'] = null;
            $this->queryParams = array_merge($this->queryParams, $request->query->all());
            if ( $this->queryParams !== null ) {
                $query = parse_url((string) $this->queryParams['returnUrl'], PHP_URL_QUERY);
                if ( $query === null) {
                    $query = [];
                } else {
                    parse_str($query,$query);
                }
                $this->queryParams = array_merge($this->queryParams, $query);
            }
        }
    }

    protected function getPaginationParameters() : array {
        return $this->queryParams;
    }

    protected function getAjax(): bool {
        if ( array_key_exists('ajax', $this->queryParams) ) {
            return $this->queryParams['ajax'] === 'true' ? true : false;
        }
        
        return false;
    }

    protected function render(string $view, array $parameters = [], Response $response = null): Response {
        $paginationParameters = $this->getPaginationParameters();
        $viewParameters = array_merge($parameters, $paginationParameters);
        return parent::render($view, $viewParameters, $response);
    }

    protected function redirectToRoute(string $route, array $parameters = [], int $status = 302): RedirectResponse {
        $paginationParameters = $this->getPaginationParameters();
        $viewParameters = array_merge($parameters, $paginationParameters);
        if ( strpos($route, '_index') || strpos($route, '_list') ) {
            unset($viewParameters['returnUrl']);
        }
        return parent::redirectToRoute($route, $viewParameters, $status);
    }

    protected function removeBlanks($criteria) {
        $new_criteria = [];
        foreach ($criteria as $key => $value) {
            if (!empty($value)) {
                $new_criteria[$key] = $value;
            }
        }

        return $new_criteria;
    }

    protected function removePaginationParameters(array $criteria) {
        unset($criteria['page'], $criteria['pageSize'], $criteria['sortName'], $criteria['sortOrder']);
        return $criteria;
    }

    protected function formatCriteria($criteria) {
        $new_criteria = [];
        foreach ($criteria as $key => $value) {
            dump($criteria, $key, $value);
            if (!empty($value) ) {
                if ( gettype($value) === "object" && $value::class === "DateTime") {
                    /** @var \DateTime $value */
                    $new_criteria[$key] = $value->format('Y-m-d H:i');
                } else {
                    $new_criteria[$key] = $value;
                }
            }
        }

        return $new_criteria;
    }

    protected function createDateTimeObjects($criteria) {
        $new_criteria = [];
        foreach ($criteria as $key => $value) {
            if ( $key === 'date_from'|| $key === 'date_to') {
                $new_criteria[$key] = DateTime::createFromFormat('Y-m-d H:i' ,$value);
            } else {
                $new_criteria[$key] = $value;
            }
        }

        return $new_criteria;
    }

    protected function setPage(int $page = 1) {
        $this->queryParams['page'] = $page;
    }
}
