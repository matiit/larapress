<?php namespace Larapress\Tests\Controllers;

use Config;
use Larapress\Tests\TestCase;
use Sentry;

class HomeControllerTest extends TestCase
{
    private $backend_route;

    public function setUp()
    {
        parent::setUp();

        $this->backend_route = Config::get('larapress.urls.backend');
    }

    /*
    |--------------------------------------------------------------------------
    | HomeController@getLogin Tests
    |--------------------------------------------------------------------------
    |
    | Here is where you can test the HomeController@getLogin method
    |
    */

    public function test_can_browse_the_login_page()
    {
        $this->call('GET', $this->backend_route . '/login');

        $this->assertResponseOk();
    }

    /*
    |--------------------------------------------------------------------------
    | HomeController@postLogin Tests
    |--------------------------------------------------------------------------
    |
    | Here is where you can test the HomeController@postLogin method
    |
    */

    public function test_can_redirect_with_a_flash_message_on_missing_login()
    {
        Sentry::shouldReceive('authenticate')
            ->with(array('email' => 'foo', 'password' => 'bar'), false)
            ->once()
            ->andThrow('Cartalyst\Sentry\Users\LoginRequiredException');

        $this->call('POST', $this->backend_route . '/login', array('email' => 'foo', 'password' => 'bar'));

        $this->assertRedirectedToRoute('larapress.home.login.get');
        $this->assertHasOldInput();
        $this->assertSessionHas('error', 'Login field is required.');
    }

    public function test_can_redirect_with_a_flash_message_on_missing_password()
    {
        Sentry::shouldReceive('authenticate')
            ->with(array('email' => 'foo', 'password' => 'bar'), false)
            ->once()
            ->andThrow('Cartalyst\Sentry\Users\PasswordRequiredException');

        $this->call('POST', $this->backend_route . '/login', array('email' => 'foo', 'password' => 'bar'));

        $this->assertRedirectedToRoute('larapress.home.login.get');
        $this->assertHasOldInput();
        $this->assertSessionHas('error', 'Password field is required.');
    }

    public function test_can_redirect_with_a_flash_message_on_wrong_password()
    {
        Sentry::shouldReceive('authenticate')
            ->with(array('email' => 'foo', 'password' => 'bar'), false)
            ->once()
            ->andThrow('Cartalyst\Sentry\Users\WrongPasswordException');

        $this->call('POST', $this->backend_route . '/login', array('email' => 'foo', 'password' => 'bar'));

        $this->assertRedirectedToRoute('larapress.home.login.get');
        $this->assertHasOldInput();
        $this->assertSessionHas('error', 'Wrong password, try again.');
    }

    public function test_can_redirect_with_a_flash_message_on_wrong_login()
    {
        Sentry::shouldReceive('authenticate')
            ->with(array('email' => 'foo', 'password' => 'bar'), false)
            ->once()
            ->andThrow('Cartalyst\Sentry\Users\UserNotFoundException');

        $this->call('POST', $this->backend_route . '/login', array('email' => 'foo', 'password' => 'bar'));

        $this->assertRedirectedToRoute('larapress.home.login.get');
        $this->assertHasOldInput();
        $this->assertSessionHas('error', 'User was not found.');
    }

    public function test_can_redirect_with_a_flash_message_on_not_activated_user()
    {
        Sentry::shouldReceive('authenticate')
            ->with(array('email' => 'foo', 'password' => 'bar'), false)
            ->once()
            ->andThrow('Cartalyst\Sentry\Users\UserNotActivatedException');

        $this->call('POST', $this->backend_route . '/login', array('email' => 'foo', 'password' => 'bar'));

        $this->assertRedirectedToRoute('larapress.home.login.get');
        $this->assertHasOldInput();
        $this->assertSessionHas('error', 'User is not activated.');
    }

    public function test_can_redirect_with_a_flash_message_on_suspended_user()
    {
        Sentry::shouldReceive('authenticate')
            ->with(array('email' => 'foo', 'password' => 'bar'), false)
            ->once()
            ->andThrow('Cartalyst\Sentry\Throttling\UserSuspendedException');

        $this->call('POST', $this->backend_route . '/login', array('email' => 'foo', 'password' => 'bar'));

        $this->assertRedirectedToRoute('larapress.home.login.get');
        $this->assertHasOldInput();
        $this->assertSessionHas('error', 'User is suspended.');
    }

    public function test_can_redirect_with_a_flash_message_on_banned_user()
    {
        Sentry::shouldReceive('authenticate')
            ->with(array('email' => 'foo', 'password' => 'bar'), false)
            ->once()
            ->andThrow('Cartalyst\Sentry\Throttling\UserBannedException');

        $this->call('POST', $this->backend_route . '/login', array('email' => 'foo', 'password' => 'bar'));

        $this->assertRedirectedToRoute('larapress.home.login.get');
        $this->assertHasOldInput();
        $this->assertSessionHas('error', 'User is banned.');
    }

    public function test_can_redirect_to_dashboard_on_success()
    {
        Sentry::shouldReceive('authenticate')->with(array('email' => 'foo', 'password' => 'bar'), false)->once();

        $this->call('POST', $this->backend_route . '/login', array('email' => 'foo', 'password' => 'bar'));

        $this->assertRedirectedToRoute('larapress.cp.dashboard.get');
    }

    /*
    |--------------------------------------------------------------------------
    | HomeController@getLogout Tests
    |--------------------------------------------------------------------------
    |
    | Here is where you can test the HomeController@getLogout method
    |
    */

    public function test_can_silently_redirect_if_the_user_is_not_logged_in()
    {
        Sentry::shouldReceive('check')->once()->andReturn(false);
        Sentry::shouldReceive('logout')->never();

        $this->call('GET', $this->backend_route . '/logout');

        $this->assertRedirectedToRoute('larapress.home.login.get');
    }

    public function test_can_log_the_user_out_and_redirect_to_the_login_page_with_a_flash_message()
    {
        Sentry::shouldReceive('check')->once()->andReturn(true);
        Sentry::shouldReceive('logout')->once();

        $this->call('GET', $this->backend_route . '/logout');

        $this->assertRedirectedToRoute('larapress.home.login.get');
        $this->assertSessionHas('success', 'You have successfully logged out.');
    }

}