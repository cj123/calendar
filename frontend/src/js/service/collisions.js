angular.module("calendar").factory("Collisions", function() {
    var collisionsFactory = {};

    function compareEvents(a, b) {
        if (a.length < b.length) {
            return -1;
        } else if (a.length > b.length) {
            return 1;
        }

        return 0;
    }

    function eventTime(momentDate) {
        return momentDate.minute() + (momentDate.hour() * 60);
    }

    /**
     * Takes a set of appointments and calculates which other appointments they collide with
     */
    collisionsFactory.calculateCollisions = function(events) {
        var timeslots = {};

        for (var i = 0; i < 1440; i++) {
            timeslots[i] = [];
        }

        events.forEach(function(event, eventIndex) {
            var start = eventTime(event.start);
            var finish = eventTime(event.finish);

            if (start === finish && finish === 0) {
                // appointments which continue entire day
                finish = 1440;
            }

            for (var slot = start; slot < finish; slot++) {
                timeslots[slot].push(eventIndex);
            }
        });

        events.forEach(function(event) {
            var start = eventTime(event.start);
            var finish = eventTime(event.finish);

            if (start === finish && finish === 0) {
                // appointments which continue entire day
                finish = 1440;
            }

            event.collisions = [];
            event.maxCollisions = 0;

            for (var slot = start; slot < finish; slot++) {
                var numCollisions = timeslots[slot].length;

                if (numCollisions === 0) {
                    continue;
                }

                if (numCollisions > event.maxCollisions) {
                    event.maxCollisions = numCollisions;

                    for (var eventIndex = 0; eventIndex < timeslots[slot].length; eventIndex++) {
                        if (event.collisions.indexOf(timeslots[slot][eventIndex]) === -1) {
                            event.collisions.push(timeslots[slot][eventIndex]);
                        }
                    }
                }
            }
        });

        events.forEach(function(event) {
            event.collisions.forEach(function(collisionIndex) {
                var collidingEvent = events[collisionIndex];

                collidingEvent.collisions.forEach(function(collision) {
                    if (event.collisions.indexOf(collision) === -1 && collisionsFactory.hasCollision(events[collision], event)) {
                        event.collisions.push(collision);
                    }
                });
            });
        });

        events.forEach(function(event) {
            event.collisions.forEach(function(collisionIndex, i) {
                event.collisions[i] = events[collisionIndex];
            });

            event.collisions.sort(compareEvents);

            event.maxCollisions = event.collisions.length;

            event.collisions.forEach(function(collisionEvent, i) {
                event.collisions[i] = collisionEvent.id;
            });
        });

        return events;
    };

    collisionsFactory.hasCollision = function(event1, event2) {
        return event1.start.isBetween(event2.start, event2.finish, 'minute', '[)');
    };

    return collisionsFactory;
});