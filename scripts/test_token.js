// test_file_key.js
// This script checks if a Figma file key is valid and if the /variables endpoint is accessible.
// It also includes a workaround to find variables in the full file content if the dedicated
// endpoint fails.

// Load environment variables from the .env file.
require("dotenv").config();
const colors = require("colors"); // Make output more readable. Install with `npm install colors`

const FIGMA_API_TOKEN = process.env.FIGMA_TOKEN;
const FIGMA_FILE_KEY = process.env.FIGMA_FILE_KEY;

// Exit if the token or key is not found.
if (!FIGMA_API_TOKEN) {
  console.error(colors.red("❌ FIGMA_TOKEN not found in .env file. Please add it."));
  process.exit(1);
}

if (!FIGMA_FILE_KEY) {
  console.error(colors.red("❌ FIGMA_FILE_KEY not found in .env file. Please add it."));
  process.exit(1);
}

console.log(colors.cyan("Using token:"), colors.yellow(FIGMA_API_TOKEN));
console.log(colors.cyan("Using file key:"), colors.yellow(FIGMA_FILE_KEY));

(async () => {
  try {
    console.log(
      colors.blue(
        "\n--- Test 1: Fetching general file data (should not fail with a valid key) ---",
      ),
    );
    // Test a general endpoint that works for any file.
    const fileUrl = `https://api.figma.com/v1/files/${FIGMA_FILE_KEY}`;
    const fileRes = await fetch(fileUrl, {
      headers: { "X-Figma-Token": FIGMA_API_TOKEN },
    });

    if (fileRes.ok) {
      console.log(colors.green("✅ Success! The file key is valid."));
      const fileData = await fileRes.json();
      console.log(
        colors.gray("File data fetched successfully. Title:"),
        colors.magenta(fileData.name),
      );
      console.log(colors.gray("Proceeding to check the /variables endpoint..."));

      // If the file key is valid, now test the /variables endpoint.
      console.log(
        colors.blue("\n--- Test 2: Fetching variables (requires a published library) ---"),
      );
      const variablesUrl = `https://api.figma.com/v1/files/${FIGMA_FILE_KEY}/variables`;
      const variablesRes = await fetch(variablesUrl, {
        headers: { "X-Figma-Token": FIGMA_API_TOKEN },
      });

      if (variablesRes.ok) {
        console.log(colors.green("✅ Success! The /variables endpoint is accessible."));
        const variablesData = await variablesRes.json();
        console.log(
          colors.gray("Variables fetched successfully. Found"),
          colors.magenta(Object.keys(variablesData.meta.variable_mode_ids).length),
          colors.gray("variable collections."),
        );
      } else {
        const variablesErr = await variablesRes.json();
        console.error(colors.red(`❌ Test 2 failed! Status: ${variablesRes.status}`));
        console.error(colors.red(`Figma API error: ${variablesErr.err}`));
        console.error(
          colors.yellow(
            "This means your file key is valid, but the variables endpoint is not working.",
          ),
        );
        console.error(
          colors.yellow(
            'Since you have an Education plan, this is likely because the file is in a "Starter" team or your Drafts, and not in a team upgraded to the "Education" plan.',
          ),
        );
        console.error(
          colors.yellow(
            "Please ensure the file is moved to a team with the correct plan and that the variables have been published as a library.",
          ),
        );

        // New workaround: find variables within the full file content
        console.log(
          colors.blue(
            "\n--- Test 3: Finding variables in the full file content (workaround for free plan) ---",
          ),
        );
        if (fileData.meta && fileData.meta.variableCollections) {
          const collections = Object.values(fileData.meta.variableCollections);
          if (collections.length > 0) {
            console.log(
              colors.green(
                `✅ Success! Found ${collections.length} variable collections in the file content.`,
              ),
            );
            collections.forEach((collection) => {
              console.log(
                colors.gray(`- Collection: `),
                colors.magenta(collection.name),
                colors.gray(`with ${collection.variableIds.length} variables`),
              );
            });
          } else {
            console.error(
              colors.red("❌ Test 3 failed! Could not find any variable collections in the file."),
            );
            console.error(colors.yellow("This means the file does not contain any variables."));
          }
        } else {
          console.error(
            colors.red(
              '❌ Test 3 failed! The file content does not have a "meta.variableCollections" key.',
            ),
          );
          console.error(colors.yellow("This file likely does not contain any variables."));
        }
      }
    } else {
      const fileErr = await fileRes.json();
      console.error(colors.red(`❌ Test 1 failed! Status: ${fileRes.status}`));
      console.error(colors.red(`Figma API error: ${fileErr.err}`));
      console.error(
        colors.yellow("The token is valid, but the API cannot find a file with that key."),
      );
      console.error(
        colors.yellow("Double-check your FIGMA_FILE_KEY in the browser URL and in your .env file."),
      );
    }
  } catch (err) {
    console.error(colors.red("An unexpected error occurred:"), err.message);
  }
})();

// require("dotenv").config();

// const FIGMA_API_TOKEN = process.env.FIGMA_TOKEN;

// (async () => {
//   try {
//     console.log("Using token:", FIGMA_API_TOKEN);
//     const res = await fetch("https://api.figma.com/v1/me", {
//       headers: { "X-Figma-Token": FIGMA_API_TOKEN },
//     });

//     const data = await res.json();
//     console.log(data);

//     if (res.ok) {
//       console.log("Token is valid! User details fetched successfully.");
//     } else {
//       console.error("Token is invalid. Figma API returned an error:", data.err);
//     }
//   } catch (err) {
//     console.error("An error occurred:", err.message);
//   }
// })();
