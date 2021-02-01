<?php
namespace tests;

use Germania\UserProfiles\Providers\PimpleServiceProvider;
use Pimple\ServiceProviderInterface;
use Pimple\Container;
use Pimple\Psr11\Container as PsrContainer;

class PimpleServiceProviderTest extends \PHPUnit\Framework\TestCase
{
    public function testRegisteringServiceProvider()
    {
        $dic = new Container;

        $sut = new PimpleServiceProvider;
        $sut->register( $dic );

        $this->assertInstanceOf(ServiceProviderInterface::class, $sut);

        $this->assertIsArray($dic['Profiles.Config']);
        $this->assertIsArray($dic['Profiles.Login']);
        $this->assertIsArray($dic['Profiles.Login.required']);
        $this->assertIsArray($dic['Profiles.ResetPassword']);
        $this->assertIsArray($dic['Profiles.ResetPassword.required']);
        $this->assertIsArray($dic['Profiles.ResetPassword.optional']);
        $this->assertIsArray($dic['Profiles.Register']);
        $this->assertIsArray($dic['Profiles.Register.required']);
        $this->assertIsArray($dic['Profiles.Register.optional']);
        $this->assertIsArray($dic['Profiles.Signup']);
        $this->assertIsArray($dic['Profiles.Signup.required']);
        $this->assertIsArray($dic['Profiles.Signup.optional']);
        $this->assertIsArray($dic['Profiles.ChangePassword']);
        $this->assertIsArray($dic['Profiles.ChangePassword.required']);
        $this->assertIsArray($dic['Profiles.ChangePassword.optional']);
        $this->assertIsArray($dic['Profiles.EditProfile']);
        $this->assertIsArray($dic['Profiles.EditProfile.required']);
        $this->assertIsArray($dic['Profiles.EditProfile.optional']);
    }
}
