<?php
namespace Germania\UserProfiles\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class PimpleServiceProvider implements ServiceProviderInterface
{

    /**
     * @param      Container $dic Pimple Dependency Injection Container
     * @implements ServiceProviderInterface
     */
    public function register(Container $dic)
    {

        $dic['Profiles.Config'] = function( $dic ) {
            $fieldconfig = [
                'required' => [],
                'optional' => []
            ];

            return [
                'reset'           => $fieldconfig,
                'change_password' => $fieldconfig,
                'change_profile'  => $fieldconfig,
                'login'           => $fieldconfig,
                'reset_password'  => $fieldconfig,
                'register'        => $fieldconfig,
                'signup'          => $fieldconfig,
                'edit_profile'    => $fieldconfig,
            ];
        };

        /**
         * @return StdClass
         */
        $dic['Profiles.Login'] = function( $dic ) {
            return $dic['Profiles.Config']['login'];
        };

        /**
         * @return array
         */
        $dic['Profiles.Login.required'] = function( $dic ) {
            return $dic['Profiles.Login']['required'];
        };



        /**
         * @return StdClass
         */
        $dic['Profiles.ResetPassword'] = function( $dic ) {
            return $dic['Profiles.Config']['reset_password'];
        };

        /**
         * @return array
         */
        $dic['Profiles.ResetPassword.required'] = function( $dic ) {
            return $dic['Profiles.ResetPassword']['required'];
        };
        /**
         * @return array
         */
        $dic['Profiles.ResetPassword.optional'] = function( $dic ) {
            return $dic['Profiles.ResetPassword']['optional'];
        };




        /**
         * @return StdClass
         */
        $dic['Profiles.Register'] = function( $dic ) {
            return $dic['Profiles.Config']['register'];
        };

        /**
         * @return array
         */
        $dic['Profiles.Register.required'] = function( $dic ) {
            return $dic['Profiles.Register']['required'];
        };

        /**
         * @return array
         */
        $dic['Profiles.Register.optional'] = function( $dic ) {
            return $dic['Profiles.Register']['optional'];
        };



        /**
         * @return StdClass
         */
        $dic['Profiles.Signup'] = function( $dic ) {
            return $dic['Profiles.Config']['signup'];
        };

        /**
         * @return array
         */
        $dic['Profiles.Signup.required'] = function( $dic ) {
            return $dic['Profiles.Signup']['required'];
        };

        /**
         * @return array
         */
        $dic['Profiles.Signup.optional'] = function( $dic ) {
            return $dic['Profiles.Signup']['optional'];
        };




        /**
         * @return StdClass
         */
        $dic['Profiles.ChangePassword'] = function( $dic ) {
            return $dic['Profiles.Config']['change_password'];
        };

        /**
         * @return array
         */
        $dic['Profiles.ChangePassword.required'] = function( $dic ) {
            return $dic['Profiles.ChangePassword']['required'];
        };
        /**
         * @return array
         */
        $dic['Profiles.ChangePassword.optional'] = function( $dic ) {
            return $dic['Profiles.ChangePassword']['optional'];
        };





        /**
         * @return StdClass
         */
        $dic['Profiles.EditProfile'] = function( $dic ) {
            return $dic['Profiles.Config']['edit_profile'];
        };

        /**
         * @return array
         */
        $dic['Profiles.EditProfile.required'] = function( $dic ) {
            return $dic['Profiles.EditProfile']['required'];
        };

        /**
         * @return array
         */
        $dic['Profiles.EditProfile.optional'] = function( $dic ) {
            return $dic['Profiles.EditProfile']['optional'];
        };

    }

}
