angular.module("calendar").factory("Collisions", [ "moment", function(moment) {
    var collisionsFactory = {};

    /**
     * Take a set of appointments and return their collision groups.
     * Each appointment in a given collision group should be treated as the same width in the frontend.
     *
     * @param events
     *
     * @returns Object
     */
    collisionsFactory.calculateCollisions = function(events) {
        var groups = [];

        for (var eventIndex = 0; eventIndex < events.length; eventIndex++) {
            var event1 = events[eventIndex];

            for (var otherEventIndex = 0; otherEventIndex < events.length; otherEventIndex++) {
                if (eventIndex === otherEventIndex) {
                    continue;
                }

                var event2 = events[otherEventIndex];

                if (this.hasCollision(event1, event2)) {
                    // find a group index with event1 in it, add event 2 to it
                    var groupIndex = this.findGroupIndex(groups, event1.id);

                    if (!(groups[groupIndex] instanceof Array)) {
                        // if we don't find anything with event 1 in it,
                        // try using groupIndex of event2 instead
                        groupIndex = this.findGroupIndex(groups, event2.id);

                        // group index using event2 failed as well
                        if (!(groups[groupIndex] instanceof Array)) {
                            groups[groupIndex] = [];
                        }
                    }

                    if (groups[groupIndex].indexOf(event1.id) === -1) {
                        groups[groupIndex].push(event1.id);
                        events[eventIndex].collisions = groups[groupIndex];
                    }
                    if (groups[groupIndex].indexOf(event2.id) === -1) {
                        groups[groupIndex].push(event2.id);
                        events[otherEventIndex].collisions = groups[groupIndex];
                    }

                }
            }
        }

        return events;
    };

    /**
     * Find the group for an appointment, or create it if it doesn't exist.
     *
     * @param groups
     * @param appointmentID
     *
     * @returns int
     */
    collisionsFactory.findGroupIndex = function(groups, appointmentID) {
        if (groups.length < 1) {
            return 0;
        }

        for (var i = groups.length - 1; i >= 0; i--) {
            if (groups[i].indexOf(appointmentID) !== -1) {
                return i;
            }
        }

        return groups.length; // new group
    };

    /**
     * Check if event1 collides with event 2
     *
     * @param event1
     * @param event2
     * @returns {boolean}
     */
    collisionsFactory.hasCollision = function(event1, event2) {
        return event1.start.isBetween(event2.start, event2.finish, 'minute', '[)');
    };

    return collisionsFactory;
}]);