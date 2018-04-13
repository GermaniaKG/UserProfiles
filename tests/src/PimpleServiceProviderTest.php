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

        $this->assertInternalType('object', $dic['Profiles.Config']);

        $this->assertInternalType('object', $dic['Profiles.Login']);
        $this->assertInternalType('array', $dic['Profiles.Login.required']);

        $this->assertInternalType('object', $dic['Profiles.ResetPassword']);
        $this->assertInternalType('array', $dic['Profiles.ResetPassword.required']);
        $this->assertInternalType('array', $dic['Profiles.ResetPassword.optional']);

        $this->assertInternalType('object', $dic['Profiles.Register']);
        $this->assertInternalType('array', $dic['Profiles.Register.required']);
        $this->assertInternalType('array', $dic['Profiles.Register.optional']);

        $this->assertInternalType('object', $dic['Profiles.Signup']);
        $this->assertInternalType('array', $dic['Profiles.Signup.required']);
        $this->assertInternalType('array', $dic['Profiles.Signup.optional']);

        $this->assertInternalType('object', $dic['Profiles.ChangePassword']);
        $this->assertInternalType('array', $dic['Profiles.ChangePassword.required']);
        $this->assertInternalType('array', $dic['Profiles.ChangePassword.optional']);

        $this->assertInternalType('object', $dic['Profiles.EditProfile']);
        $this->assertInternalType('array', $dic['Profiles.EditProfile.required']);
        $this->assertInternalType('array', $dic['Profiles.EditProfile.optional']);
    }
}
