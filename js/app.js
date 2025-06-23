/**
 * Main application logic for PromptPay Web App
 * Handles navigation, form events, QR code generation, and bill splitting.
 *
 * Depends on: PromptPayQR (js/PromptPayQR.js), QRCode.js (external)
 */
import PromptPayQR from './js/PromptPayQR.js';

// Store QRCode instances for each view
const qrCodeInstances = {};

document.addEventListener('DOMContentLoaded', () => {
    // --- View Elements ---
    const homeView = document.getElementById('homeView');
    const generatorView = document.getElementById('generatorView');
    const splitBillView = document.getElementById('splitBillView');

    // --- Navigation ---
    function showView(viewId) {
        document.querySelectorAll('.view').forEach(v => v.classList.add('hidden'));
        document.getElementById(viewId).classList.remove('hidden');
    }

    // --- Generator View Setup ---
    function setupGeneratorView(withAmount) {
        showView('generatorView');
        const template = document.getElementById('generator-template').content.cloneNode(true);
        generatorView.innerHTML = '';
        const header = document.createElement('div');
        header.className = 'flex items-center mb-8';
        header.innerHTML = `
             <button class="back-button p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 mr-4">
                <svg class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
             </button>
            <h1 id="generatorTitle" class="text-2xl sm:text-3xl font-bold text-gray-800 dark:text-white"></h1>
        `;
        generatorView.appendChild(header);
        generatorView.appendChild(template);
        generatorView.querySelector('.back-button').addEventListener('click', () => showView('homeView'));
        const title = generatorView.querySelector('#generatorTitle');
        const form = generatorView.querySelector('#qrForm');
        const amountField = generatorView.querySelector('#amountField');
        if (withAmount) {
            title.textContent = 'สร้าง QR สำหรับจ่ายเงิน';
            amountField.style.display = 'block';
            form.querySelector('#amount').required = true;
        } else {
            title.textContent = 'สร้าง QR สำหรับรับเงิน';
            amountField.style.display = 'none';
            form.querySelector('#amount').required = false;
        }
        attachFormListeners(form);
    }

    // --- Form Listeners for QR Generation ---
    function attachFormListeners(form) {
        let qrInstanceKey = form.closest('.view').id;
        form.addEventListener('submit', function (event) {
            event.preventDefault();
            const container = form.closest('.view');
            const errorMessage = container.querySelector('.error-message');
            const qrCodeContainer = container.querySelector('#qrCodeContainer');
            errorMessage.classList.add('hidden');
            const promptpayIdInput = form.querySelector('#promptpayId').value.trim();
            const amountInput = form.querySelector('#amount').required ? parseFloat(form.querySelector('#amount').value) : null;
            if (!promptpayIdInput) {
                showError(errorMessage, qrCodeContainer, 'กรุณาระบุ PromptPay ID');
                return;
            }
            if (form.querySelector('#amount').required && (isNaN(amountInput) || amountInput <= 0)) {
                showError(errorMessage, qrCodeContainer, 'กรุณาระบุจำนวนเงินที่ถูกต้อง');
                return;
            }
            const payload = PromptPayQR.genText(promptpayIdInput, amountInput);
            if (!payload) {
                showError(errorMessage, qrCodeContainer, 'รูปแบบ PromptPay ID ไม่ถูกต้อง');
                return;
            }
            generateQRCode(qrInstanceKey, qrCodeContainer, payload, amountInput);
        });
    }

    // --- QR Code Generation ---
    function generateQRCode(instanceKey, qrContainer, payload, amount = null) {
        const qrcodeDiv = qrContainer.querySelector('.qrcode-display');
        const qrAmountText = qrContainer.querySelector('.qr-amount-text');
        if (qrcodeDiv) {
            if (qrCodeInstances[instanceKey]) {
                qrcodeDiv.innerHTML = '';
            }
            qrCodeInstances[instanceKey] = new QRCode(qrcodeDiv, {
                text: payload, width: 256, height: 256,
                colorDark: '#000000', colorLight: '#ffffff', correctLevel: QRCode.CorrectLevel.M
            });
        }
        if (qrAmountText) {
            if (amount) {
                qrAmountText.textContent = `จำนวน: ${amount.toFixed(2)} บาท`;
                qrAmountText.classList.remove('hidden');
            } else {
                qrAmountText.classList.add('hidden');
            }
        }
        qrContainer.classList.remove('hidden');
        qrContainer.classList.add('flex');
        const downloadBtn = qrContainer.querySelector('.download-btn');
        downloadBtn.onclick = () => {
            const canvas = qrcodeDiv.getElementsByTagName('canvas')[0];
            if (canvas) {
                const link = document.createElement('a');
                link.download = `promptpay-qr-${Date.now()}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();
            }
        };
    }

    // --- Bill Splitter Logic ---
    const splitBillForm = document.getElementById('splitBillForm');
    splitBillForm.addEventListener('submit', function (event) {
        event.preventDefault();
        const qrContainer = document.getElementById('splitQrCodeContainer');
        const errorMessage = document.getElementById('split-error-message');
        errorMessage.classList.add('hidden');
        const myId = document.getElementById('myPromptpayId').value.trim();
        const total = parseFloat(document.getElementById('totalAmount').value);
        const people = parseInt(document.getElementById('numPeople').value);
        if (!myId || !total || !people) {
            showError(errorMessage, qrContainer, 'กรุณากรอกข้อมูลให้ครบทุกช่อง');
            return;
        }
        if (total <= 0 || people < 2) {
            showError(errorMessage, qrContainer, 'ยอดเงินต้องมากกว่า 0 และจำนวนคนต้องมีอย่างน้อย 2 คน');
            return;
        }
        const amountPerPerson = total / people;
        const payload = PromptPayQR.genText(myId, amountPerPerson);
        if (!payload) {
            showError(errorMessage, qrContainer, 'รูปแบบ PromptPay ID ของคุณไม่ถูกต้อง');
            return;
        }
        document.getElementById('splitResultText').textContent = `จ่ายคนละ: ${amountPerPerson.toFixed(2)} บาท`;
        generateQRCode('split', qrContainer, payload, amountPerPerson);
    });

    // --- Navigation Event Listeners ---
    document.getElementById('showGeneratorWithAmount').addEventListener('click', () => setupGeneratorView(true));
    document.getElementById('showGeneratorWithoutAmount').addEventListener('click', () => setupGeneratorView(false));
    document.getElementById('showSplitBill').addEventListener('click', () => {
        document.getElementById('splitBillForm').reset();
        document.getElementById('splitQrCodeContainer').classList.add('hidden');
        document.getElementById('split-error-message').classList.add('hidden');
        showView('splitBillView');
    });
    document.querySelectorAll('.back-button').forEach(btn => btn.addEventListener('click', () => showView('homeView')));

    // --- Error Display Utility ---
    function showError(errorElement, qrContainer, message) {
        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
        if (qrContainer) {
            qrContainer.classList.add('hidden');
            qrContainer.classList.remove('flex');
        }
    }
});
