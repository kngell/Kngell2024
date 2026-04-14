module.exports = (prop) => {
    let debug = "// --- PROP OBJECT DEBUG ---\n";
    
    // Get all properties and methods
    let props = [];
    let methods = [];
    
    // Get all keys including prototype
    let obj = prop;
    while (obj !== null) {
        Object.getOwnPropertyNames(obj).forEach(propName => {
            if (typeof obj[propName] === 'function') {
                methods.push(propName);
            } else {
                props.push(propName);
            }
        });
        obj = Object.getPrototypeOf(obj);
    }
    
    // Remove duplicates
    methods = [...new Set(methods)].filter(m => m !== 'constructor');
    props = [...new Set(props)];
    
    debug += "// Available properties:\n";
    props.forEach(p => debug += `// - ${p}\n`);
    
    debug += "\n// Available methods:\n";
    methods.forEach(m => debug += `// - ${m}\n`);
    
    // Try to get actual values
    debug += "\n// Trying to get property info:\n";
    try {
        if (prop.getName) {
            debug += `// getName() = "${prop.getName()}"\n`;
        }
    } catch (e) { debug += `// getName() failed: ${e.message}\n`; }
    
    try {
        if (prop.getType) {
            debug += `// getType() = "${prop.getType()}"\n`;
        }
    } catch (e) { debug += `// getType() failed: ${e.message}\n`; }
    
    try {
        if (prop.isNullable) {
            debug += `// isNullable() = ${prop.isNullable()}\n`;
        }
    } catch (e) { debug += `// isNullable() failed: ${e.message}\n`; }
    
    try {
        if (prop.getNameOriginal) {
            debug += `// getNameOriginal() = "${prop.getNameOriginal()}"\n`;
        }
    } catch (e) { debug += `// getNameOriginal() failed: ${e.message}\n`; }
    
    try {
        if (prop.getterName) {
            debug += `// getterName() = "${prop.getterName()}"\n`;
        }
    } catch (e) { debug += `// getterName() failed: ${e.message}\n`; }
    
    try {
        if (prop.getterDescription) {
            debug += `// getterDescription() = "${prop.getterDescription()}"\n`;
        }
    } catch (e) { debug += `// getterDescription() failed: ${e.message}\n`; }
    
    return debug;
};
