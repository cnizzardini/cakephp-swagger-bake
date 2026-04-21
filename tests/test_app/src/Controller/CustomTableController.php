<?php
declare(strict_types=1);

namespace SwaggerBakeTest\App\Controller;

class CustomTableController extends AppController
{
    public ?string $defaultTable = 'Employees';

    public function index(): void
    {
    }

    public function view($id = null): void
    {
    }
}
