<?php
declare(strict_types=1);

namespace App;

use App\Model\Table\UsersTable;
use App\Model\View\Account\LoginViewModel;
use Authentication\AuthenticationService;
use Authentication\AuthenticationServiceInterface;
use Authentication\AuthenticationServiceProviderInterface;
use Authentication\Identifier\AbstractIdentifier;
use Authentication\Middleware\AuthenticationMiddleware;
use Cake\Core\Configure;
use Cake\Core\ContainerInterface;
use Cake\Datasource\FactoryLocator;
use Cake\Error\Middleware\ErrorHandlerMiddleware;
use Cake\Http\BaseApplication;
use Cake\Http\Middleware\BodyParserMiddleware;
use Cake\Http\Middleware\CsrfProtectionMiddleware;
use Cake\Http\MiddlewareQueue;
use Cake\ORM\Locator\LocatorAwareTrait;
use Cake\ORM\Locator\TableLocator;
use Cake\Routing\Middleware\AssetMiddleware;
use Cake\Routing\Middleware\RoutingMiddleware;
use Cake\Routing\Router;
use DateTime;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Application setup class.
 *
 * @extends BaseApplication<Application>
 */
class Application extends BaseApplication implements AuthenticationServiceProviderInterface
{
  #region traits

  use LocatorAwareTrait;

  #endregion

  #region public methods

  /**
   * Load all the application configuration and bootstrap logic.
   *
   * @return void
   */
  public function bootstrap(): void
  {
    // Call parent to load bootstrap from files.
    parent::bootstrap();

    if (PHP_SAPI !== 'cli') {
      FactoryLocator::add(
        'Table',
        (new TableLocator())->allowFallbackClass(false)
      );
    }
  }

  /**
   * Setup the middleware queue your application will use.
   *
   * @param \Cake\Http\MiddlewareQueue $middlewareQueue The middleware queue to setup.
   * @return \Cake\Http\MiddlewareQueue The updated middleware queue.
   */
  public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
  {
    $middlewareQueue
      // Catch any exceptions in the lower layers,
      // and make an error page/response
      ->add(new ErrorHandlerMiddleware(Configure::read('Error'), $this))

      // Handle plugin/theme assets like CakePHP normally does.
      ->add(new AssetMiddleware([
        'cacheTime' => Configure::read('Asset.cacheTime'),
      ]))

      // Add routing middleware.
      // If you have a large number of routes connected, turning on routes
      // caching in production could improve performance.
      // See https://github.com/CakeDC/cakephp-cached-routing
      ->add(new RoutingMiddleware($this))

      // Parse various types of encoded request bodies so that they are
      // available as array through $request->getData()
      // https://book.cakephp.org/5/en/controllers/middleware.html#body-parser-middleware
      ->add(new BodyParserMiddleware())

      // Cross Site Request Forgery (CSRF) Protection Middleware
      // https://book.cakephp.org/5/en/security/csrf.html#cross-site-request-forgery-csrf-middleware
      ->add(new CsrfProtectionMiddleware([
        'httponly' => true,
      ]))

      // Add the AuthenticationMiddleware. It should be
      // after routing and body parser.
      ->add(new AuthenticationMiddleware($this));

    return $middlewareQueue;
  }

  /**
   * Register application container services.
   *
   * @param ContainerInterface $container The Container to update.
   *
   * @return void
   *
   * @link https://book.cakephp.org/5/en/development/dependency-injection.html#dependency-injection
   */
  public function services(ContainerInterface $container): void
  {
  }

  #endregion

  #region AuthenticationServiceProviderInterface

  public function getAuthenticationService(ServerRequestInterface $request
  ): AuthenticationServiceInterface {
    $service = new AuthenticationService();
    // define where users should be redirected to when they are not authenticated
    $service->setConfig([
      'unauthenticatedRedirect' => Router::url([
        'prefix' => false,
        'plugin' => null,
        'controller' => 'Account',
        'action' => 'login',
      ]),
      'queryParam' => 'redirect',
    ]);
    $fields = [
      AbstractIdentifier::CREDENTIAL_USERNAME => LoginViewModel::EMAIL,
      AbstractIdentifier::CREDENTIAL_PASSWORD => LoginViewModel::PASSWORD
    ];
    // Load the authenticators. Session should be first.
    $service->loadAuthenticator('Authentication.Session');
    $service->loadAuthenticator('Authentication.Form', [
      'fields' => $fields,
    ]);
    $service->loadAuthenticator('Authentication.Cookie', [
      'rememberMeField' => LoginViewModel::REMEMBER_ME,
      'cookie' => [
        'name' => 'CookieRememberMe',
        'expires' => new DateTime('+1 year'),
        'secure' => true,
        'httponly' => true
      ],
      'fields' => $fields,
    ]);
    // load identifiers
    $service->loadIdentifier(
      'Authentication.Password',
      [
        'fields' => $fields,
        'resolver' => [
          'className' => 'Authentication.Orm',
          'userModel' => UsersTable::class,
        ],
      ]);
    return $service;
  }

  #endregion
}
