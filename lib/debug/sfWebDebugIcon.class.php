<?php
/*
 * This file is part of the Sift PHP framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * sfWebDebugIcon provides icons for web debug. Provides icons as data uris.
 *
 * @package    Sift
 * @subpackage debug
 */
class sfWebDebugIcon
{
    /**
     * Array of icons
     *
     * @var array
     */
    protected static $icons
        = array(
            'user'     => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA0AAAAPCAYAAAA/I0V3AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAACcgAAAnIBJZ1kXQAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAJYSURBVCiRZZJBaxNRFIXPezPjOEycOhQqpWlKk6kEgoE0JBaKVLsoxdrfUOjClThk4aYuCiIBUbJSyapU/QX+AEPclkeiIu2mmUIwJGCmhMZnM5lknhsbUr3Lc/m4l3MOEUIAAPb29q6ZpvnWsqz7mqbpiqLInuf94pw/TqVS7zE2RAiBYrF4l1L6bnV1NaLr+vgeQgg0Go0nmUzm1YVGAYBSmtd1/T8AAAghMAzj2bgmxWIx3ff91xMTE3R2dhacc7RaLZycnMBxHBwdHeHs7EwulUoim81+BgCSz+cfEEI+mqZJe70eOp3OpUuKomAwGIh+v8/D4fAHVVV3JQCpWq22mcvlZMYYhBCQJAmxWAxBEGB7exuJRILs7+9fWVlZuREKhW7TTqdzc2FhgUxPTyOZTCIajcK2bayvr8N1XQwGA0xNTcF1Xb9YLD4ihLyUAfxst9sBAKytrV16a2dnBwDg+z76/b7UarXqGxsbX2kQBKxer6unp6fodrtoNpvwfR+u66JcLqPb7aJarYIQ4luW9X2U0/Ly8qfFxcU7uVxOYYyh3W7DMAwkEgnMzMxga2vLc133xcHBwe4IWlpaClNKv83Pz1+3bZvE43H0ej1UKhUUCgXBOW94nhdljPkAIAOALMu3Jicnf5+fn5u2bWM4HCIIAmiahrm5OaKq6lXHcR4CeAMAJJlM6pFI5Es6nbYuasM5hyRJ0DRtZMzh4eEPx3GyjLEmNU3zaTwej47XJhQKXQIAwLKssGEYzwFAHg6Hm8fHxzUhBCGEjNh/+kf+Wn8PAP4AwEv/Bnp5BwgAAAAASUVORK5CYII=',
            'database' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAoAAAAOCAYAAAAWo42rAAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAACnQAAAp0BG2kiKQAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAAAGWSURBVCiRhY69jhJxHABnl/8Cy56R3HnZeDY0ZyGNL2BhaeID2FzjI1gZCt7ChJgYe8uLnTbakCgnRoOiCBx4ByucsLgfsh/sz8bYWDDJdFOMJiKcnZ0+1HO5u4WCeaOQz5dBE2fuLIeDQceZnD87Orr/SDuffG9bJetmoWBSr9dpNpt4nke1WqVWq3Hxc0av33vJ/OJHFgS+9Ho9sW1bAAFE13VpNBry5WtXnjx9HKnlaqEBHBxcZTweMRqN8X2PSqVCEAS0Tt6QSearKF7jzCcoZWAog6KZR8vt8P5Dm9PhkNl8xu7ebqL4S5ompGnCb0J8z8ddLVlHawBExNSjKGYbQRBe0iUjWq1+ISL/BVmWsVi4bLJMU0Uz71ol0/b9gCAM8Dz/n6Bj2/tYOxYKYCMbTKuIaRW5sr9HGIRMpw7O1MH3EwD0IAytrZOA3vnYcbdFg/4wzr1rvW3HcXIvp5QqXy6jlCJJElzXpfu5S6t1kh0fP3+giQiapl0D7hiGcevw+uFtybJSr//tVRqnr4EXIvLpDw4b3jMGI0eBAAAAAElFTkSuQmCC',
        );

    /**
     * Returns the icon
     *
     * @param string $name
     */
    public static function get($name)
    {
        return isset(self::$icons[$name]) ? self::$icons[$name] : null;
    }

}
