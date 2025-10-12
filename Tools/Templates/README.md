# PHP Getters & Setters Templates

This folder contains custom templates used by the `phproberto.vscode-php-getters-setters` extension.

How to test locally

1. Run the included test script to print the generated getter/setter for a sample property:

```bash
node Tools/Templates/test-templates.js
```

2. Ensure VS Code uses the workspace settings by reloading the window (Developer: Reload Window).

3. If the extension still inserts the default template:
   - Open the Output panel and select the extension's output to see any template-load errors.
   - Make sure your workspace `.vscode/settings.json` contains the three keys: `templatesDir`, `getterTemplate`, `setterTemplate`.

Notes

- Templates must be valid JS modules (they export a function that receives a `property` object and returns the PHP string).
- This repo includes a `test-templates.js` to validate template output outside VS Code.
