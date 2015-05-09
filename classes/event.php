<?php

// File:        event.php
// Author:      Brian Allen Vanderburg II
// Purpose:     A simple event dispatcher/hook 

namespace mrbavii\MyBoard;

/**
 * An event dispatching class
 */
class Event
{
    protected static $events = array();
    protected static $queues = array();
    protected static $defaults = array();

    /**
     * Listen for an event.
     *
     * @param event The name or an array of names of the event to listen for.
     * @param callback The closure to call when the event is fired.
     */
    public static function listen($event, $callback)
    {
        if(!isset(static::$events[$event]))
        {
            static::$events[$event] = array();
        }
        static::$events[$event][] = $callback;
    }

    /**
     * Clear all callbacks for an event
     *
     * @param event The name of the event to clear callbacks for.
     */
    public static function clear($event)
    {
        if(isset(static::$events[$event]))
        {
            unset(static::$events[$event]);
        }
    }

    /**
     * Register a default callack.
     * If no callbacks are registered then the initial callback will be called.
     * Else only the regisred callbacks will be called.
     *
     * @param event The event to listen for.
     * @param callback The default callback for the event.  Use null to clear
     *  the default callback;
     */
    public static function initial($event, $callback)
    {
        if($callback !== null)
        {
            static::$defaults[$event] = $callback;
        }
        else
        {
            unset(static::$defaults[$event]);
        }
    }

    /**
     * Override an event.
     * This clears all other callbacks for the event.  It does not clear
     * the default callback.
     *
     * @param event The name of the event to listen for.
     * @param callback The closure to call when the event is fired.
     */
    public static function override($event, $callback)
    {
        static::clear($event);
        static::register($event, $callback);
    }

    /**
     * Fire an event
     *
     * @param event The name of the event to fire.
     * @param args Additional arguments are passed to the callbacks
     * @param until Fire until the first non-null response if TRUE.
     * @return Array of all return values if until is FALSE. The first non-null
       response or null if until is TRUE;
     */
    public static function fire($event, $args=array(), $until=FALSE)
    {
        $results = array();

        $handled = FALSE;
        if(isset(static::$events[$event]))
        {
            foreach(static::$events[$event] as $callback)
            {
                $handled = TRUE;
                $result = call_user_func_array($callback, $args);
                if($until && $result !== null)
                {
                    return $result;
                }
                $results[] = $result;
            }
        }

        if(!$handled && isset(static::$defaults[$event]))
        {
            $result = call_user_func_array(static::$defaults[$event], $args);
            if($until && $result !== null)
            {
                return $result;
            }
            $results[] = $result;
        }

        return $until ? null : $results;
    }

    /**
     * Fire an event until a return that is not null.
     *
     * @param event The name of the event to fire.
     * @param args Additional arguments are passed to the callbacks
     * @return The value of the non-null return, or null if no non-null results.
     */
    public static function until($event, $args=array())
    {
        return static::fire($event, $args, TRUE);
    }

    /**
     * Queue an event for a later firing.
     *
     * @param event The name of the event to queue
     * @param args Additional arguments passed to the callbacks
     */
    public static function queue($event, $args=array())
    {
        if(!isset(static::$queues[$event]))
        {
            static::$queues[$event] = array();
        }

        static::$queues[$event][] = $args;
    }

    /**
     * Flush an event queue.
     * This will call all listeners of an event each time with the arguments
     * of the queued events.
     *
     * @param queue The queue to flush
     */
    public static function flush($event)
    {
        if(isset(static::$queues[$event]))
        {
            foreach(static::$queues[$event] as $args)
            {
                static::fire($event, $args);
            }
            unset(static::$queues[$event]);
        }
    }
}

