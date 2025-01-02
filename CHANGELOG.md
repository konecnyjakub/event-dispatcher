Version 2.1.0-dev
- added DummyEventDispatcher
- fixed method DebugEventDispatcher::dispatched sometimes returning incorrect result

Version 2.0.0
- removed classes ListenerProvider and PriorityListenerProvider and method ChainListenerProvider::registerProvider
- added option to register multiple listeners from an object in AutoListenerProvider

Version 1.5.0
- deprecated PriorityListenerProvider in favor of AutoListenerProvider

Version 1.4.0
- possible BC break: PriorityListenerProvider/ListenerProvider::getListenersForEvent() now returns a Generator instead of array
- added AutoListenerProvider

Version 1.3.0
- deprecated ChainListenerProvider::registerProvider in favor of new addProvider()
- added constants PRIORITY_HIGH, PRIORITY_NORMAL and PRIORITY_LOW to PriorityListenerProvider
- added method DebugEventDispatcher::dispatched

Version 1.2.0
- allowed registering multiple listeners at the same time in PriorityListenerProvider
- added default priority for listeners in PriorityListenerProvider
- added DebugEventDispatcher
- added support for event subscribers
- deprecated ListenerProvider
- deprecated PriorityListenerProvider::registerListener in favor of new addListener()

Version 1.1.0
- allowed registering multiple listeners at the same time in ListenerProvider

Version 1.0.1
- internal improvements

Version 1.0.0
- initial version
