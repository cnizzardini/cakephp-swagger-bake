<?php
declare(strict_types=1);
namespace SwaggerBake\Test;

use Cake\Controller\Controller;

/**
 * This is used just for setting up something paginate properties
 */
class PaginationTestController extends Controller
{
    protected array $paginate = [];

    public function setPaginate(array $paginate)
    {
        $this->paginate = $paginate;
    }
}