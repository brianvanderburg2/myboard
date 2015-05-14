MyBoard - Copyright (C) 2015 Brian Allen Vanderburg II

MyBoard is a personal board/forum meant to be used either by itself or along
an existing website.  It is primarily intended for use by myself, however is
made available in case anyone else is interested.  It is not to be confused with
MyBB, which is also a forum software.

Features
========

Configuration Items
===================

Framework
---------
app.dispatcher.index (DEFAULT: "/index")
    The index page.  If there is no path information, this is the default
    path information to redirect to.
app.dispatcher.class (REQUIRED)
    The root dispatcher class.  If the derived application does not overrid
    the dispatch method, the internal dispatch method get the dispatch service
    which uses this configuration as the class name.
app.error.enable_handler (DEFAULT: FALSE)
    Whether to enable the internal error handlers or not.  If set, then errors
    and exceptions will be handled by App::errorHandler and App::exceptionHandler.
app.error.report_all (DEFAULT: TRUE)
    Whether to set error reporting to E_ALL.  If FALSE, the error reporting is
    not changed.
app.error.show_user (DEFAULT: FALSE)
    If TRUE, display_errors will be turned on, otherwise it will be turned off.
app.appdata.dir (REQUIRED if used)
    The data directory for application data files.  Used by default services
    such as Template
app.userdata.dir (REQUIRED if used)
    The data directory for user data files.  Used by default services such
    as Template
app.sendfile.method (DEFAULT: "readfile")
    The method to use to send files to the client.
app.sendfile.options
    Options to pass to the file reader.


MyBoard
-------
