<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         3.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Shim\Test\TestCase\Routing\Route;

use Cake\Core\App;
use Cake\Routing\Router;
use Shim\Routing\Route\InflectedRoute;
use Cake\TestSuite\TestCase;

/**
 * Test case for InflectedRoute
 */
class InflectedRouteTest extends TestCase
{

	/**
	 * test that routes match their pattern.
	 *
	 * @return void
	 */
	public function testMatchBasic()
	{
		$route = new InflectedRoute('/:controller/:action/:id', ['plugin' => null]);
		$result = $route->match(['controller' => 'Posts', 'action' => 'myView', 'plugin' => null]);
		$this->assertFalse($result);

		$result = $route->match([
			'plugin' => null,
			'controller' => 'Posts',
			'action' => 'myView',
			0
		]);
		$this->assertFalse($result);

		$result = $route->match([
			'plugin' => null,
			'controller' => 'MyPosts',
			'action' => 'myView',
			'id' => 1
		]);
		$this->assertEquals('/my_posts/my_view/1', $result);

		$route = new InflectedRoute('/', ['controller' => 'Pages', 'action' => 'myDisplay', 'home']);
		$result = $route->match(['controller' => 'Pages', 'action' => 'myDisplay', 'home']);
		$this->assertEquals('/', $result);

		$result = $route->match(['controller' => 'Pages', 'action' => 'display', 'about']);
		$this->assertFalse($result);

		$route = new InflectedRoute('/blog/:action', ['controller' => 'Posts']);
		$result = $route->match(['controller' => 'Posts', 'action' => 'myView']);
		$this->assertEquals('/blog/my_view', $result);

		$result = $route->match(['controller' => 'Posts', 'action' => 'myView', 'id' => 2]);
		$this->assertEquals('/blog/my_view?id=2', $result);

		$result = $route->match(['controller' => 'Posts', 'action' => 'myView', 1]);
		$this->assertFalse($result);

		$route = new InflectedRoute('/foo/:controller/:action', ['action' => 'index']);
		$result = $route->match(['controller' => 'Posts', 'action' => 'myView']);
		$this->assertEquals('/foo/posts/my_view', $result);

		$route = new InflectedRoute('/:plugin/:id/*', ['controller' => 'Posts', 'action' => 'myView']);
		$result = $route->match([
			'plugin' => 'TestPlugin',
			'controller' => 'Posts',
			'action' => 'myView',
			'id' => '1'
		]);
		$this->assertEquals('/test_plugin/1/', $result);

		$result = $route->match([
			'plugin' => 'TestPlugin',
			'controller' => 'Posts',
			'action' => 'myView',
			'id' => '1',
			'0'
		]);
		$this->assertEquals('/test_plugin/1/0', $result);

		$result = $route->match([
			'plugin' => 'TestPlugin',
			'controller' => 'Nodes',
			'action' => 'myView',
			'id' => 1
		]);
		$this->assertFalse($result);

		$result = $route->match([
			'plugin' => 'TestPlugin',
			'controller' => 'Posts',
			'action' => 'edit',
			'id' => 1
		]);
		$this->assertFalse($result);

		$route = new InflectedRoute('/admin/subscriptions/:action/*', [
			'controller' => 'Subscribe', 'prefix' => 'admin'
		]);
		$result = $route->match([
			'controller' => 'Subscribe',
			'prefix' => 'admin',
			'action' => 'editAdminE',
			1
		]);
		$expected = '/admin/subscriptions/edit_admin_e/1';
		$this->assertEquals($expected, $result);

		$route = new InflectedRoute('/:controller/:action_:id');
		$result = $route->match([
			'controller' => 'MyPosts',
			'action' => 'myView',
			'id' => 1
		]);
		$this->assertEquals('/my_posts/my_view_1', $result);

		$route = new InflectedRoute('/:controller/:action/:slug_:id', [], ['id' => Router::ID]);
		$result = $route->match([
			'controller' => 'MyPosts',
			'action' => 'myView',
			'id' => 1,
			'slug' => 'the_slug'
		]);
		$this->assertEquals('/my_posts/my_view/the_slug_1', $result);
	}

	/**
	 * test the parse method of InflectedRoute.
	 *
	 * @return void
	 */
	public function testParse()
	{
		$route = new InflectedRoute('/:controller/:action/:id', [], ['id' => Router::ID]);
		$route->compile();
		$result = $route->parse('/my_posts/my_view/1');
		$this->assertEquals('MyPosts', $result['controller']);
		$this->assertEquals('myView', $result['action']);
		$this->assertEquals('1', $result['id']);

		$route = new InflectedRoute('/:controller/:action_:id');
		$route->compile();
		$result = $route->parse('/my_posts/my_view_1');
		$this->assertEquals('MyPosts', $result['controller']);
		$this->assertEquals('myView', $result['action']);
		$this->assertEquals('1', $result['id']);

		$route = new InflectedRoute('/:controller/:action/:slug_:id', [], ['id' => Router::ID]);
		$route->compile();
		$result = $route->parse('/my_posts/my_view/the_slug_1');
		$this->assertEquals('MyPosts', $result['controller']);
		$this->assertEquals('myView', $result['action']);
		$this->assertEquals('1', $result['id']);
		$this->assertEquals('the_slug', $result['slug']);

		$route = new InflectedRoute(
			'/admin/:controller',
			['prefix' => 'admin', 'action' => 'index']
		);
		$route->compile();
		$result = $route->parse('/admin/');
		$this->assertFalse($result);

		$result = $route->parse('/admin/my_posts');
		$this->assertEquals('MyPosts', $result['controller']);
		$this->assertEquals('index', $result['action']);

		$route = new InflectedRoute(
			'/media/search/*',
			['controller' => 'Media', 'action' => 'searchIt']
		);
		$result = $route->parse('/media/search');
		$this->assertEquals('Media', $result['controller']);
		$this->assertEquals('searchIt', $result['action']);
		$this->assertEquals([], $result['pass']);

		$result = $route->parse('/media/search/tv_shows');
		$this->assertEquals('Media', $result['controller']);
		$this->assertEquals('searchIt', $result['action']);
		$this->assertEquals(['tv_shows'], $result['pass']);
	}

	/**
	 * @return void
	 */
	public function testMatchThenParse()
	{
		$route = new InflectedRoute('/plugin/:controller/:action', [
			'plugin' => 'Vendor/PluginName'
		]);
		$url = $route->match([
			'plugin' => 'Vendor/PluginName',
			'controller' => 'ControllerName',
			'action' => 'actionName'
		]);
		$expectedUrl = '/plugin/controller_name/action_name';
		$this->assertEquals($expectedUrl, $url);
		$result = $route->parse($expectedUrl);
		$this->assertEquals('ControllerName', $result['controller']);
		$this->assertEquals('actionName', $result['action']);
		$this->assertEquals('Vendor/PluginName', $result['plugin']);
	}

}
