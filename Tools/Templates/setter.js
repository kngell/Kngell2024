module.exports = (prop) => {
  try {
    const name = prop.getName();
    const nameUcFirst = name.charAt(0).toUpperCase() + name.slice(1);
    const type = prop.getType();
    const nullable = prop.isNullable();

    const paramType = type ? (nullable ? `?${type} ` : `${type} `) : "";
    const docParamType = type ? (nullable ? `null|${type}` : type) : "mixed";

    // Robust class name detection
    let className = "self";
    try {
      const vscode = require("vscode");
      const editor = vscode.window.activeTextEditor;
      if (editor) {
        const document = editor.document;
        const text = document.getText();

        // Get first 50 lines to avoid processing huge files
        let firstLines = "";
        const lineCount = Math.min(document.lineCount, 50);
        for (let i = 0; i < lineCount; i++) {
          firstLines += document.lineAt(i).text + "\n";
        }

        // Look for class definition in the first 50 lines
        // This avoids most comments but focuses on the actual class definition
        const lines = firstLines.split("\n");
        for (const line of lines) {
          const trimmed = line.trim();

          // Skip comment lines
          if (trimmed.startsWith("//") || trimmed.startsWith("*") || trimmed.startsWith("/*")) {
            continue;
          }

          // Match class definitions (abstract, final, readonly, normal)
          const classMatch = trimmed.match(/(?:abstract\s+|final\s+|readonly\s+)*class\s+(\w+)/);
          if (classMatch && classMatch[1]) {
            className = classMatch[1];
            break;
          }

          // Match interfaces, enums, traits
          const interfaceMatch = trimmed.match(/interface\s+(\w+)/);
          const enumMatch = trimmed.match(/enum\s+(\w+)/);
          const traitMatch = trimmed.match(/trait\s+(\w+)/);

          if (interfaceMatch && interfaceMatch[1]) {
            className = interfaceMatch[1];
            break;
          }
          if (enumMatch && enumMatch[1]) {
            className = enumMatch[1];
            break;
          }
          if (traitMatch && traitMatch[1]) {
            className = traitMatch[1];
            break;
          }
        }
      }
    } catch (vscodeError) {
      // If detection fails, fall back to 'self'
      className = "self";
    }

    return `/**
 * @param ${docParamType} $${name}
 * @return ${className}
 */
public function set${nameUcFirst}(${paramType}$${name}): ${className}
{
    $this->${name} = $${name};

    return $this;
}
`;
  } catch (error) {
    // Fallback template
    const name = prop.getName();
    const nameUcFirst = name.charAt(0).toUpperCase() + name.slice(1);
    return `public function set${nameUcFirst}($${name}): self { $this->${name} = $${name}; return $this; }`;
  }
};
