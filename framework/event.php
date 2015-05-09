<?php

// File:        event.php
// Author:      Brian Allen Vanderburg II
// Purpose:     A simple event dispatcher/hook 

namespace mrbavii\Framework;

/**
 * An event dispatching class
 */
class Event
{
    protected $events = array();
    protected $queues = array();
    protected $defaults = array();

    /**
     * Listen for an event.
     *
     * \param event The name or an array of names of the event to listen for.
     * \param callback The closure to call when the event is fired.
     */
    public function listen($event, $callback)
    {
        if(!isset($this->events[$event]))
        {
            $this->events[$event] = array();
        }
        $this->events[$event][] = $callback;
    }

    /**
     * Clear all callbacks for an event
     *
     * \param event The name of the event to clear callbacks for.
     */
    public function clear($event)
    {
        if(isset($this->events[$event]))
        {
            unset($this->events[$event]);
        }
    }

    /**
     * Register a default callack.
     * If no callbacks are registered then the initial callback will be called.
     * Else only the regisred callbacks will be called.
     *
     * \param event The event to listen for.
     * \param callback The default callback for the event.  Use null to clear
     *  the default callback;
     */
    public function initial($event, $callback)
    {
        if($callback !== null)
        {
            $this->defaults[$event] = $callback;
        }
        else
        {
            unset($this->defaults[$event]);
        }
    }

    /**
     * Fire an event
     *
     * \param event The name of the event to fire.
     * \param args Additional arguments are passed to the callbacks
     * \param until Fire until the first non-null response if TRUE.
     * \return Array of all return values if until is FALSE. The first non-null
       response or null if until is TRUE;
     */
    public function fire($event, $args=array(), $until=FALSE)
    {
        $results = array();

        $handled = FALSE;
        if(isset($this->events[$event]))
        {
            foreach($this->events[$event] as $callback)
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

        if(!$handled && isset($this->defaults[$event]))
        {
            $result = call_user_func_array($this->defaults[$event], $args);
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
     * \param event The name of the event to fire.
     * \param args Additional arguments are passed to the callbacks
     * \return The value of the non-null return, or null if no non-null results.
     */
    public function until($event, $args=array())
    {
        return $this->fire($event, $args, TRUE);
    }

    /**
     * Queue an event for a later firing.
     *
     * \param event The name of the event to queue
     * \param args Additional arguments passed to the callbacks
     */
    public function queue($event, $args=array())
    {
        if(!isset($this->queues[$event]))
        {
            $this->queues[$event] = array();
        }

        $this->queues[$event][] = $args;
    }

    /**
     * Flush an event queue.
     * This will call all listeners of an event each time with the arguments
     * of the queued events.
     *
     * \param queue The queue to flush
     */
    public function flush($event)
    {
        if(isset($this->queues[$event]))
        {
            foreach($this->queues[$event] as $args)
            {
                $this->fire($event, $args);
            }
            unset($this->queues[$event]);
        }
    }
}

