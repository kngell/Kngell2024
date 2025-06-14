/**
 * ImportFiles class
 *
 * Handles importing files (like images, SVGs, etc.) for webpack processing.
 * When webpack builds the application, it will copy these files to the public directory.
 */
export default class ImportFiles {
  /**
   * Constructor
   */
  constructor() {
    this.importedFiles = [];
  }

  /**
   * Import all files from a webpack context
   *
   * @param {Object} context - A webpack context created using require.context
   * @returns {Array} - Array of imported files
   */
  importAll = (context) => {
    if (!context || typeof context.keys !== "function") {
      console.error("Invalid context provided to ImportFiles.importAll");
      return [];
    }

    // Get all file keys (paths) from the context
    const keys = context.keys();

    // Map each key to its imported module
    this.importedFiles = keys.map((key) => {
      const module = context(key);
      console.log(
        `Imported: ${key} → ${typeof module === "string" ? module : "[Module]"}`
      );
      return module;
    });

    return this.importedFiles;
  };

  /**
   * Get all imported files
   *
   * @returns {Array} - Array of imported files
   */
  getImportedFiles() {
    return this.importedFiles;
  }
}
