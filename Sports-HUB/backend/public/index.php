<?php

declare(strict_types=1);

use App\Core\Container;
use App\Core\Database;
use App\Core\Env;
use App\Core\SimpleRouter;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CourtController;
use App\Http\Request;
use App\Repositories\BookingRepository;
use App\Repositories\CourtRepository;
use App\Repositories\UserRepository;
use App\Security\AuthGuard;
use App\Security\JwtService;
use App\Services\AdminService;
use App\Services\AuthService;
use App\Services\BookingService;
use App\Services\CourtService;

require dirname(__DIR__) . '/vendor/autoload.php';

$rootPath = dirname(__DIR__);
Env::bootstrap($rootPath);

$container = new Container();

$container->set(Database::class, static fn (): Database => new Database());
$container->set(PDO::class, static fn (Container $c): PDO => $c->get(Database::class)->getConnection());
$container->set(UserRepository::class, static fn (Container $c): UserRepository => new UserRepository($c->get(PDO::class)));
$container->set(CourtRepository::class, static fn (Container $c): CourtRepository => new CourtRepository($c->get(PDO::class)));
$container->set(BookingRepository::class, static fn (Container $c): BookingRepository => new BookingRepository($c->get(PDO::class)));
$container->set(JwtService::class, static fn (): JwtService => new JwtService());
$container->set(AuthGuard::class, static fn (Container $c): AuthGuard => new AuthGuard($c->get(JwtService::class), $c->get(UserRepository::class)));
$container->set(AuthService::class, static fn (Container $c): AuthService => new AuthService($c->get(UserRepository::class), $c->get(JwtService::class)));
$container->set(CourtService::class, static fn (Container $c): CourtService => new CourtService($c->get(CourtRepository::class), $c->get(BookingRepository::class)));
$container->set(BookingService::class, static fn (Container $c): BookingService => new BookingService($c->get(BookingRepository::class), $c->get(CourtRepository::class)));
$container->set(AdminService::class, static fn (Container $c): AdminService => new AdminService($c->get(UserRepository::class), $c->get(CourtRepository::class), $c->get(BookingRepository::class)));
$container->set(AuthController::class, static fn (Container $c): AuthController => new AuthController($c->get(AuthService::class)));
$container->set(CourtController::class, static fn (Container $c): CourtController => new CourtController($c->get(CourtService::class)));
$container->set(BookingController::class, static fn (Container $c): BookingController => new BookingController($c->get(BookingService::class), $c->get(AuthGuard::class)));
$container->set(AdminController::class, static fn (Container $c): AdminController => new AdminController(
    $c->get(AuthGuard::class),
    $c->get(AdminService::class),
    $c->get(CourtService::class),
    $c->get(BookingService::class)
));

$dispatcher = new SimpleRouter();
$dispatcher->addRoute('POST', '/auth/register', [AuthController::class, 'register']);
$dispatcher->addRoute('POST', '/auth/login', [AuthController::class, 'login']);

$dispatcher->addRoute('GET', '/courts', [CourtController::class, 'index']);
$dispatcher->addRoute('GET', '/courts/{id:\d+}/slots', [CourtController::class, 'slots']);

$dispatcher->addRoute('POST', '/bookings', [BookingController::class, 'store']);
$dispatcher->addRoute('GET', '/bookings', [BookingController::class, 'index']);

$dispatcher->addRoute('GET', '/admin/dashboard', [AdminController::class, 'dashboard']);
$dispatcher->addRoute('GET', '/admin/courts', [AdminController::class, 'listCourts']);
$dispatcher->addRoute('POST', '/admin/courts', [AdminController::class, 'createCourt']);
$dispatcher->addRoute('GET', '/admin/courts/{id:\d+}', [AdminController::class, 'getCourt']);
$dispatcher->addRoute('PUT', '/admin/courts/{id:\d+}', [AdminController::class, 'updateCourt']);
$dispatcher->addRoute('DELETE', '/admin/courts/{id:\d+}', [AdminController::class, 'deleteCourt']);
$dispatcher->addRoute('GET', '/admin/bookings', [AdminController::class, 'listBookings']);
$dispatcher->addRoute('POST', '/admin/bookings', [AdminController::class, 'createBooking']);
$dispatcher->addRoute('GET', '/admin/bookings/{id:\d+}', [AdminController::class, 'getBooking']);
$dispatcher->addRoute('PUT', '/admin/bookings/{id:\d+}', [AdminController::class, 'updateBooking']);
$dispatcher->addRoute('PATCH', '/admin/bookings/{id:\d+}/cancel', [AdminController::class, 'cancelBooking']);
$dispatcher->addRoute('DELETE', '/admin/bookings/{id:\d+}', [AdminController::class, 'deleteBooking']);

$httpMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$uri = strtok($uri, '?') ?: '/';

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

if ($httpMethod === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, Authorization');
    http_response_code(204);
    exit;
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

switch ($routeInfo[0]) {
    case 0: // NOT_FOUND
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['message' => 'Not Found'], JSON_THROW_ON_ERROR);
        break;

    case 1: // FOUND
        [$handler, $vars] = [$routeInfo[1], $routeInfo[2]];
        $request = new Request();
        handle($handler, $vars, $request, $container);
        break;
}

/**
 * @param callable|array{0: class-string, 1: string} $handler
 * @param array<string, string> $vars
 */
function handle($handler, array $vars, Request $request, Container $container): void
{
    if (is_array($handler)) {
        [$class, $method] = $handler;
        $controller = $container->get($class);
        $reflection = new ReflectionMethod($controller, $method);
        $parameters = $reflection->getParameters();

        $args = [];
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            if ($type !== null && !$type->isBuiltin()) {
                $typeName = $type->getName();
                if ($typeName === Request::class) {
                    $args[] = $request;
                    continue;
                }
            }

            if ($parameter->getName() === 'args' || $parameter->getName() === 'params') {
                $args[] = $vars;
            }
        }

        $controller->{$method}(...$args);

        return;
    }

    if (is_callable($handler)) {
        $handler($vars, $request, $container);
    }
}

