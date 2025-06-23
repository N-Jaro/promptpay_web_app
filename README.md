# PromptPay QR Generator

A simple, client-side web application to generate Thai PromptPay QR codes for various payment scenarios. This tool is built with plain HTML, CSS (Tailwind CSS), and JavaScript, making it lightweight and easy to deploy.

## ✨ Features

This web app provides three main functionalities:

1.  **Create QR for Payment:** Generate a QR code for a specific amount. Ideal for invoicing or requesting a fixed payment from a buyer.
2.  **Create QR for Receiving Funds:** Generate a static QR code without a pre-filled amount. Perfect for sellers, merchants, or receiving donations where the payer specifies the amount.
3.  **Split the Bill:** A handy utility for groups. Enter the total bill amount and the number of people, and the app calculates the amount per person, generating a specific QR code for that share. This makes it easy to collect money from friends.

**Other Features:**

* **Download QR Code:** Easily save the generated QR code as a PNG image.
* **Responsive Design:** Works smoothly on both desktop and mobile browsers.
* **No Backend Required:** Runs entirely in the browser, ensuring privacy and simplicity.

## 🚀 How to Use

As this is a static web application, there is no installation or build process required.

**Option 1: Local Usage**

1.  Clone or download this repository.
2.  Open the `index.html` file directly in your web browser (e.g., Chrome, Firefox, Safari).

**Option 2: Deployment**

1.  Upload the three files (`index.html`, `style.css`, `script.js`) to any static web hosting service, such as:
    * GitHub Pages
    * Netlify
    * Vercel
    * Cloudflare Pages

## 📂 File Structure

The repository is organized into three main files:

* `index.html`: The main HTML file containing the structure of the web application, including all views and forms.
* `style.css`: Contains custom CSS rules. The project primarily uses [Tailwind CSS](https://tailwindcss.com/) via a CDN for styling.
* `script.js`: Holds all the application logic, including:
    * PromptPay payload generation.
    * UI navigation between the home screen, payment generator, and bill splitter.
    * Form handling and input validation.
    * QR code rendering and download functionality.

## 🙏 Acknowledgements

This project utilizes the following open-source libraries and resources:

* **PromptPay QR Logic:** The core QR code payload generation logic is based on the implementation found at [diewland.github.io/promptpay-qr-js/](https://diewland.github.io/promptpay-qr-js/).
* **QR Code Rendering:** Uses the [qrcode.js](https://github.com/davidshimjs/qrcodejs) library by davidshimjs to render the QR code image onto the HTML canvas.
* **Styling:** Styled with [Tailwind CSS](https://tailwindcss.com/) for a modern, responsive user interface.

*This project is intended for personal and educational purposes. Always verify the recipient's name in your banking app before confirming any payment.*
