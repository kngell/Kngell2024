module.exports = (prop) => {
  const name = prop.getName();
  const nameUcFirst = name.charAt(0).toUpperCase() + name.slice(1);
  const type = prop.getType();
  const nullable = prop.isNullable();

  // This extension handles types properly!
  const paramType = type ? (nullable ? `?${type} ` : `${type} `) : "";
  const docParamType = type ? (nullable ? `null|${type}` : type) : "mixed";

  // Extract class name from file
  let className = "self";
  try {
    const vscode = require("vscode");
    const editor = vscode.window.activeTextEditor;
    if (editor) {
      const text = editor.document.getText();
      const classMatch = text.match(/class\s+(\w+)\s+/);
      if (classMatch && classMatch[1]) {
        className = classMatch[1];
      }
    }
  } catch (e) {
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
};
