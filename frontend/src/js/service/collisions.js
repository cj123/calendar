angular.module("calendar").factory("Collisions", function() {
    var collisionsFactory = {};

    function compareEvents(a, b) {
        if (a.length < b.length) {
            return -1;
        }
        if (a.length > b.length) {
            return 1;
        }
        // a must be equal to b
        return 0;
    }

    /**
     * Take a set of appointments and return their collision groups.
     * Each appointment in a given collision group should be treated as the same width in the frontend.
     *
     * @param events
     *
     * @returns Object
     */
    collisionsFactory.calculateCollisions = function(events) {
        var timeslots = {};

        for (var i = 0; i < 1440; i++) {
            timeslots[i] = [];
        }

        events.forEach(function(event, eventIndex) {
            var start = event.start.minute() + (event.start.hour() * 60);
            var finish = event.finish.minute() + (event.finish.hour() * 60);

            for (var slot = start; slot < finish; slot++) {
                timeslots[slot].push(eventIndex);
            }
        });

        /*for (i = 0; i < 1440; i++) {
            timeslots[i].sort();
        }*/

        events.forEach(function(event) {
            var start = event.start.minute() + (event.start.hour() * 60);
            var finish = event.finish.minute() + (event.finish.hour() * 60);

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

        // go through and look at each conflicting appointment and which it conflicts with.
        /*events.forEach(function(event) {
            event.collisions.forEach(function(collisionIndex) {
                var collidingEvent = events[collisionIndex];

                collidingEvent.collisions.forEach(function(collision) {
                    if (event.collisions.indexOf(collision) === -1) {
                        event.collisions.push(collision);
                    }
                });
            });
        });*/

        events.forEach(function(event) {
            event.collisions.forEach(function(collisionIndex, i) {
                event.collisions[i] = events[collisionIndex];
            });

            event.collisions.sort(compareEvents);

            event.collisions.forEach(function(collisionEvent, i) {
                event.collisions[i] = collisionEvent.id;
            });
        });

        return events;
    };

    return collisionsFactory;
});