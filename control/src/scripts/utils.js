// Filter objects by properties
// Exmaple => Object.filter(object, (f) => { return f === 1 }));
Object.filter = function(obj, predicate) {
    let result = {},
        key;

    for (key in obj) {
        if (obj.hasOwnProperty(key) && predicate(obj[key])) {
            result[key] = obj[key];
        }
    }

    return result;
};