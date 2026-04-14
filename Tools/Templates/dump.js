module.exports = (property) => {
    let output = "// === PROPERTY DUMP ===\n";
    
    // Try to get all methods and properties
    let methods = [];
    let props = [];
    
    let obj = property;
    while (obj !== null) {
        Object.getOwnPropertyNames(obj).forEach(name => {
            if (typeof obj[name] === 'function') {
                methods.push(name);
            } else {
                props.push(name);
            }
        });
        obj = Object.getPrototypeOf(obj);
    }
    
    methods = [...new Set(methods)].filter(m => m !== 'constructor');
    props = [...new Set(props)];
    
    output += "// Available methods:\n";
    methods.forEach(m => output += `// - ${m}\n`);
    
    output += "\n// Available properties:\n";
    props.forEach(p => output += `// - ${p}\n`);
    
    // Try to call common methods
    output += "\n// Attempting to call methods:\n";
    
    const testMethods = ['getName', 'getPropertyName', 'getFieldName', 'getterName', 'getOriginalName', 'getLabel', 'getDescription'];
    testMethods.forEach(method => {
        if (typeof property[method] === 'function') {
            try {
                output += `// ${method}() = "${property[method]()}"\n`;
            } catch (e) {
                output += `// ${method}() failed: ${e.message}\n`;
            }
        }
    });
    
    return output;
};
