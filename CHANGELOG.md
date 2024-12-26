Version 1.3.0+dev
- possible BC break: PriorityListenerProvider/ListenerProvider::getListenersForEvent() now returns a Generator instead of array

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
