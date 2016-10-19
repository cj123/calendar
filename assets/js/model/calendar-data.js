"use strict";

var moment = require("moment");

module.exports = {
    currentDate: null,

    init: function() {
        this.currentDate = moment();
    }
};
