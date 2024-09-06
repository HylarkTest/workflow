import { ApolloLink, Observable } from '@apollo/client/core';

// Turn `subscribe` arguments into an observer-like thing, see getObserver
// https://github.com/apollographql/subscriptions-transport-ws/blob/master/src/client.ts#L347-L361
function getObserver(
    observerOrNext,
    onError,
    onComplete
) {
    if (typeof observerOrNext === 'function') {
        // Duck-type an observer
        return {
            next: (v) => observerOrNext(v),
            error: (e) => onError && onError(e),
            complete: () => onComplete && onComplete(),
        };
    }
    // Make an object that calls to the given object, with safety checks
    return {
        next: (v) => observerOrNext.next && observerOrNext.next(v),
        error: (e) => observerOrNext.error && observerOrNext.error(e),
        complete: () => observerOrNext.complete && observerOrNext.complete(),
    };
}

class PusherLink extends ApolloLink {
    constructor(options) {
        super();
        // Retain a handle to the Pusher client
        this.pusher = options.pusher;
    }

    request(operation, forward) {
        const subscribeObservable = new Observable(() => { });
        // Capture the super method
        const prevSubscribe = subscribeObservable.subscribe.bind(subscribeObservable);
        // Override subscribe to return an `unsubscribe` object, see
        // https://github.com/apollographql/subscriptions-transport-ws/blob/master/src/client.ts#L182-L212
        subscribeObservable.subscribe = (
            observerOrNext,
            onError,
            onComplete
        ) => {
            // Call super
            if (typeof (observerOrNext) === 'function') {
                prevSubscribe(observerOrNext, onError, onComplete);
            } else {
                prevSubscribe(observerOrNext);
            }
            const observer = getObserver(observerOrNext, onError, onComplete);

            let subscriptionChannel;
            // Check the result of the operation
            const resultObservable = forward(operation);
            // When the operation is done, try to get the subscription ID from the server
            resultObservable.subscribe({
                next: (data) => {
                    // Check to see if the response has the header
                    subscriptionChannel = data?.extensions?.lighthouse_subscriptions.channel ?? null;
                    if (subscriptionChannel) {
                    // Set up the pusher subscription for updates from the server
                        const pusherChannel = this.pusher.subscribe(subscriptionChannel);
                        // Pass along the initial payload:
                        if (data.data && Object.keys(data.data).length > 0) {
                            observer.next(data);
                        }
                        // Subscribe for more update
                        pusherChannel.bind('lighthouse-subscription', (payload) => {
                            this._onUpdate(subscriptionChannel, observer, payload);
                        });
                    } else {
                    // This isn't a subscription,
                    // So pass the data along and close the observer.
                        observer.next(data);
                        observer.complete();
                    }
                },
                error: observer.error,
                // complete: observer.complete Don't pass this because Apollo unsubscribes if you do
            });

            // Return an object that will unsubscribe _if_ the query was a subscription.
            return {
                closed: false,
                unsubscribe: () => {
                    if (subscriptionChannel) {
                        this.pusher.unsubscribe(subscriptionChannel);
                    }
                },
            };
        };
        return subscribeObservable;
    }

    _onUpdate(subscriptionChannel, observer, payload) {
        const result = payload.result;
        if (result) {
            // Send the new response to listeners
            observer.next(result);
        }
        if (!payload.more) {
            // This is the end, the server says to unsubscribe
            this.pusher.unsubscribe(subscriptionChannel);
            observer.complete();
        }
    }
}

export default PusherLink;
