# Dark Mode Implementation

This document outlines how the dark mode feature is implemented in the PILAR Asset Inventory System and how to apply it to other pages.

## Files Added/Modified

1. **`/includes/dark_mode_helper.php`**
   - Handles dark mode state management
   - Provides functions to check and toggle dark mode
   - Includes JavaScript for handling dark mode toggle

2. **`/css/dark-mode.css`**
   - Contains all dark mode styles
   - Uses CSS variables for easy theming
   - Includes styles for common Bootstrap components

3. **`/includes/header.php`**
   - Main header file that includes all necessary CSS and scripts
   - Initializes dark mode on page load
   - Includes the dark mode toggle button

4. **`/includes/footer.php`**
   - Contains JavaScript for handling dark mode toggle
   - Includes all necessary scripts
   - Closes the HTML document

5. **`/index.php`**
   - Updated to use the new header and footer includes
   - Includes page-specific dark mode styles

## How to Add Dark Mode to Other Pages

1. **At the top of your PHP file**, add:
   ```php
   <?php
   $pageTitle = 'Your Page Title';
   require_once "includes/header.php";
   ?>
   ```

2. **At the bottom of your PHP file**, add:
   ```php
   <?php require_once "includes/footer.php"; ?>
   ```

3. **For page-specific dark mode styles**, add them in a `<style>` tag in your PHP file:
   ```html
   <style>
   /* Your dark mode styles here */
   .dark-mode .your-element {
       background-color: #2d2d2d;
       color: #f8f9fa;
   }
   </style>
   ```

## How It Works

1. **State Management**:
   - Dark mode state is stored in the PHP session
   - The state persists across page reloads
   - Toggling dark mode sends an AJAX request to update the session

2. **Styling**:
   - Dark mode styles are applied using the `.dark-mode` class on the `html` and `body` elements
   - CSS variables are used for consistent theming
   - All Bootstrap components are styled for dark mode

3. **Toggle Button**:
   - The dark mode toggle button is included in the header
   - It shows a sun icon in dark mode and a moon icon in light mode
   - The button updates the UI without a page reload

## Customization

1. **Colors**:
   - Edit the CSS variables in `/css/dark-mode.css` to change the color scheme
   - The main variables are defined at the top of the file

2. **Components**:
   - Add or modify styles for specific components in `/css/dark-mode.css`
   - Use the `.dark-mode` prefix to target elements in dark mode

3. **JavaScript**:
   - The main JavaScript for dark mode is in `/includes/footer.php`
   - The `handleDarkModeToggle` function handles the toggle functionality

## Browser Support

- The dark mode feature works in all modern browsers
- The toggle uses the `classList` API, which is supported in all modern browsers
- The feature degrades gracefully in older browsers

## Notes

- The dark mode state is tied to the user's session
- If you want to persist the preference across sessions, you'll need to implement local storage or a database solution
- Always test new pages in both light and dark modes to ensure readability and contrast
