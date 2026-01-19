<?php

/**
 * Prayer Times UI Shortcode
 * Displays interactive prayer times with location selector, tabs, and navigation
 * Usage: [prayer_times_ui]
 */

class DateConversion
{
    public function __construct()
    {
        add_shortcode('date_conversion_sh', [$this, 'render_date_conversion']);
    }

    /**
     * Render the Date Conversion UI
     */
    public function render_date_conversion()
    {
        ob_start();
?>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }

            /* body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 20px;
            } */

            .container {
                background-color: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                width: 100%;
                max-width: 800px;
                overflow: hidden;
            }

            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                text-align: center;
            }

            .header h1 {
                font-size: 2.5rem;
                margin-bottom: 10px;
            }

            .header p {
                opacity: 0.9;
                font-size: 1.1rem;
            }

            .converter-container {
                display: flex;
                flex-direction: column;
                padding: 40px;
            }

            .converter-form {
                display: grid;
                grid-template-columns: 1fr auto 1fr;
                gap: 30px;
                align-items: start;
            }

            .converter-box {
                background-color: #f8f9fa;
                border-radius: 15px;
                padding: 30px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            }

            .converter-title {
                font-size: 1.8rem;
                color: #333;
                margin-bottom: 25px;
                text-align: center;
                padding-bottom: 15px;
                border-bottom: 2px solid #667eea;
            }

            .date-selectors {
                display: grid;
                gap: 20px;
            }

            .selector-group {
                display: flex;
                flex-direction: column;
                gap: 8px;
            }

            .selector-group label {
                font-weight: 600;
                color: #555;
                font-size: 1.1rem;
            }

            .selector-group select,
            .selector-group input {
                padding: 15px;
                border: 2px solid #e0e0e0;
                border-radius: 10px;
                font-size: 1.1rem;
                transition: all 0.3s ease;
                background-color: white;
            }

            .selector-group select:focus,
            .selector-group input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }

            .calendar-container {
                position: relative;
            }

            .calendar-grid {
                background: white;
                border: 2px solid #667eea;
                border-radius: 10px;
                padding: 15px;
                margin-top: 10px;
                display: none;
            }

            .calendar-grid.active {
                display: block;
            }

            .calendar-header {
                text-align: center;
                font-weight: 600;
                color: #667eea;
                margin-bottom: 10px;
                font-size: 1.1rem;
            }

            .calendar-days {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 5px;
            }

            .calendar-day-header {
                text-align: center;
                font-weight: 600;
                color: #555;
                padding: 8px;
                font-size: 0.9rem;
            }

            .calendar-day {
                text-align: center;
                padding: 10px;
                cursor: pointer;
                border-radius: 5px;
                transition: all 0.2s ease;
                background-color: #f8f9fa;
            }

            .calendar-day:hover {
                background-color: #667eea;
                color: white;
            }

            .calendar-day.selected {
                background-color: #667eea;
                color: white;
                font-weight: 600;
            }

            .calendar-day.disabled {
                opacity: 0.3;
                cursor: not-allowed;
            }

            .calendar-day.disabled:hover {
                background-color: #f8f9fa;
                color: inherit;
            }

            .switch-container {
                display: flex;
                justify-content: center;
                align-items: center;
                margin-top: 40px;
            }

            .switch-btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 50%;
                width: 70px;
                height: 70px;
                font-size: 1.5rem;
                cursor: pointer;
                display: flex;
                justify-content: center;
                align-items: center;
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
                transition: all 0.3s ease;
            }

            .switch-btn:hover {
                transform: scale(1.1);
                box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
            }

            .switch-btn:active {
                transform: scale(0.95);
            }

            .convert-btn-container {
                grid-column: 1 / -1;
                display: flex;
                justify-content: center;
                margin-top: 20px;
            }

            .convert-btn {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 15px;
                padding: 18px 50px;
                font-size: 1.3rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            }

            .convert-btn:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 30px rgba(102, 126, 234, 0.4);
            }

            .convert-btn:active {
                transform: translateY(0);
            }

            .result-container {
                grid-column: 1 / -1;
                background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                border-radius: 15px;
                padding: 30px;
                margin-top: 30px;
                text-align: center;
                color: white;
                display: none;
            }

            .result-container.active {
                display: block;
                animation: fadeIn 0.5s ease;
            }

            .result-title {
                font-size: 1.5rem;
                margin-bottom: 15px;
                opacity: 0.9;
            }

            .result-date {
                font-size: 2.5rem;
                font-weight: 700;
                margin-bottom: 10px;
            }

            .result-details {
                font-size: 1.2rem;
                opacity: 0.9;
            }

            .loader {
                display: none;
                justify-content: center;
                margin: 20px 0;
            }

            .loader.active {
                display: flex;
            }

            .spinner {
                border: 5px solid #f3f3f3;
                border-top: 5px solid #667eea;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
            }

            @keyframes spin {
                0% {
                    transform: rotate(0deg);
                }

                100% {
                    transform: rotate(360deg);
                }
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @media (max-width: 768px) {
                .converter-form {
                    grid-template-columns: 1fr;
                    gap: 20px;
                }

                .switch-container {
                    margin-top: 0;
                    margin-bottom: 20px;
                }

                .switch-btn {
                    transform: rotate(90deg);
                }

                .switch-btn:hover {
                    transform: rotate(90deg) scale(1.1);
                }

                .header h1 {
                    font-size: 2rem;
                }

                .converter-container {
                    padding: 25px;
                }
            }
        </style>
        <div class="container">
            <div class="header">
                <h1>Gregorian ↔ Hijri Date Converter</h1>
                <p>Convert dates between Gregorian and Hijri calendars</p>
            </div>

            <div class="converter-container">
                <div class="converter-form" id="converterForm">
                    <!-- Gregorian to Hijri -->
                    <div class="converter-box" id="gregorianBox">
                        <h2 class="converter-title">Gregorian to Hijri</h2>
                        <div class="date-selectors">
                            <div class="selector-group">
                                <label for="dcGregorianYear">Year</label>
                                <select id="dcGregorianYear"></select>
                            </div>

                            <div class="selector-group">
                                <label for="gregorianMonth">Month</label>
                                <select id="gregorianMonth">
                                    <option value="1">January</option>
                                    <option value="2">February</option>
                                    <option value="3">March</option>
                                    <option value="4">April</option>
                                    <option value="5">May</option>
                                    <option value="6">June</option>
                                    <option value="7">July</option>
                                    <option value="8">August</option>
                                    <option value="9">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                            </div>

                            <div class="selector-group calendar-container">
                                <label for="gregorianDayInput">Day</label>
                                <input type="text" id="gregorianDayInput" value="18" readonly style="cursor: pointer;">
                                <div class="calendar-grid" id="gregorianCalendar">
                                    <div class="calendar-header" id="gregorianCalendarHeader">January 2026</div>
                                    <div class="calendar-days" id="gregorianCalendarDays"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Switch Button -->
                    <div class="switch-container">
                        <button class="switch-btn" id="switchBtn">⇄</button>
                    </div>

                    <!-- Hijri to Gregorian -->
                    <div class="converter-box" id="hijriBox" style="display: none;">
                        <h2 class="converter-title">Hijri to Gregorian</h2>
                        <div class="date-selectors">
                            <div class="selector-group">
                                <label for="dcHijriYear">Year</label>
                                <select id="dcHijriYear"></select>
                            </div>

                            <div class="selector-group">
                                <label for="hijriMonth">Month</label>
                                <select id="hijriMonth">
                                    <option value="1">Muharram</option>
                                    <option value="2">Safar</option>
                                    <option value="3">Rabi' al-Awwal</option>
                                    <option value="4">Rabi' al-Thani</option>
                                    <option value="5">Jumada al-Awwal</option>
                                    <option value="6">Jumada al-Thani</option>
                                    <option value="7">Rajab</option>
                                    <option value="8">Sha'ban</option>
                                    <option value="9">Ramadan</option>
                                    <option value="10">Shawwal</option>
                                    <option value="11">Dhu al-Qi'dah</option>
                                    <option value="12">Dhu al-Hijjah</option>
                                </select>
                            </div>

                            <div class="selector-group calendar-container">
                                <label for="hijriDayInput">Day</label>
                                <input type="text" id="hijriDayInput" value="1" readonly style="cursor: pointer;">
                                <div class="calendar-grid" id="hijriCalendar">
                                    <div class="calendar-header" id="hijriCalendarHeader">Muharram 1447</div>
                                    <div class="calendar-days" id="hijriCalendarDays"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Convert Button -->
                    <div class="convert-btn-container">
                        <button class="convert-btn" id="convertBtn">Convert Date</button>
                    </div>

                    <!-- Loader -->
                    <div class="loader" id="loader">
                        <div class="spinner"></div>
                    </div>

                    <!-- Result Container -->
                    <div class="result-container" id="resultContainer">
                        <h3 class="result-title" id="resultTitle">Conversion Result</h3>
                        <div class="result-date" id="resultDate"></div>
                        <div class="result-details" id="resultDetails"></div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            // DOM Elements
            const gregorianBox = document.getElementById('gregorianBox');
            const hijriBox = document.getElementById('hijriBox');
            const switchBtn = document.getElementById('switchBtn');
            const convertBtn = document.getElementById('convertBtn');
            const resultContainer = document.getElementById('resultContainer');
            const resultTitle = document.getElementById('resultTitle');
            const resultDate = document.getElementById('resultDate');
            const resultDetails = document.getElementById('resultDetails');
            const loader = document.getElementById('loader');

            // Gregorian inputs
            const gregorianYear = document.getElementById('dcGregorianYear');
            const gregorianMonth = document.getElementById('gregorianMonth');
            const gregorianDayInput = document.getElementById('gregorianDayInput');
            const gregorianCalendar = document.getElementById('gregorianCalendar');
            const gregorianCalendarHeader = document.getElementById('gregorianCalendarHeader');
            const gregorianCalendarDays = document.getElementById('gregorianCalendarDays');

            // Hijri inputs
            const hijriYear = document.getElementById('dcHijriYear');
            const hijriMonth = document.getElementById('hijriMonth');
            const hijriDayInput = document.getElementById('hijriDayInput');
            const hijriCalendar = document.getElementById('hijriCalendar');
            const hijriCalendarHeader = document.getElementById('hijriCalendarHeader');
            const hijriCalendarDays = document.getElementById('hijriCalendarDays');


            // Populate Gregorian years (1900-2100)
            function populateGregorianYears() {
                let html = '';
                for (let year = 1900; year <= 2100; year++) {
                    html += '<option value="' + year + '">' + year + '</option>';
                }
                gregorianYear.innerHTML = html;
            }

            // Populate Hijri years (1300-1500)
            function populateHijriYears() {
                let html = '';
                for (let year = 1300; year <= 1500; year++) {
                    html += '<option value="' + year + '">' + year + '</option>';
                }
                hijriYear.innerHTML = html;
            }

            // Initialize year dropdowns
            populateGregorianYears();
            populateHijriYears();

            let selectedGregorianDay = 18;
            let selectedHijriDay = 1;

            // Set current date
            const today = new Date();
            gregorianYear.value = today.getFullYear();
            gregorianMonth.value = today.getMonth() + 1;
            gregorianDayInput.value = today.getDate();
            selectedGregorianDay = today.getDate();

            // Toggle between Gregorian to Hijri and Hijri to Gregorian
            let isGregorianToHijri = true;

            switchBtn.addEventListener('click', function() {
                isGregorianToHijri = !isGregorianToHijri;

                if (isGregorianToHijri) {
                    gregorianBox.style.display = 'block';
                    hijriBox.style.display = 'none';
                    resultContainer.classList.remove('active');
                } else {
                    gregorianBox.style.display = 'none';
                    hijriBox.style.display = 'block';
                    resultContainer.classList.remove('active');
                }
            });

            // Calendar functions for Gregorian
            function generateGregorianCalendar() {
                const year = parseInt(gregorianYear.value);
                const month = parseInt(gregorianMonth.value);
                const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

                gregorianCalendarHeader.textContent = `${monthNames[month - 1]} ${year}`;

                const firstDay = new Date(year, month - 1, 1).getDay();
                const daysInMonth = new Date(year, month, 0).getDate();

                let html = '';
                const dayHeaders = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
                dayHeaders.forEach(day => {
                    html += `<div class="calendar-day-header">${day}</div>`;
                });

                for (let i = 0; i < firstDay; i++) {
                    html += `<div class="calendar-day disabled"></div>`;
                }

                for (let day = 1; day <= daysInMonth; day++) {
                    const selectedClass = day === selectedGregorianDay ? 'selected' : '';
                    html += `<div class="calendar-day ${selectedClass}" data-day="${day}">${day}</div>`;
                }

                gregorianCalendarDays.innerHTML = html;

                gregorianCalendarDays.querySelectorAll('.calendar-day:not(.disabled)').forEach(dayEl => {
                    dayEl.addEventListener('click', function() {
                        selectedGregorianDay = parseInt(this.getAttribute('data-day'));
                        gregorianDayInput.value = selectedGregorianDay;
                        gregorianCalendar.classList.remove('active');
                        generateGregorianCalendar();
                    });
                });
            }

            gregorianDayInput.addEventListener('click', function() {
                gregorianCalendar.classList.toggle('active');
                generateGregorianCalendar();
            });

            gregorianMonth.addEventListener('change', function() {
                generateGregorianCalendar();
            });

            // Calendar functions for Hijri
            function generateHijriCalendar() {
                const year = parseInt(hijriYear.value);
                const month = parseInt(hijriMonth.value);
                const monthNames = ["Muharram", "Safar", "Rabi' al-Awwal", "Rabi' al-Thani", "Jumada al-Awwal", "Jumada al-Thani", "Rajab", "Sha'ban", "Ramadan", "Shawwal", "Dhu al-Qi'dah", "Dhu al-Hijjah"];

                hijriCalendarHeader.textContent = `${monthNames[month - 1]} ${year}`;

                let html = '';
                const dayHeaders = ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
                dayHeaders.forEach(day => {
                    html += `<div class="calendar-day-header">${day}</div>`;
                });

                // Hijri months can have 29 or 30 days, we'll use 30 as default
                const daysInMonth = 30;

                for (let day = 1; day <= daysInMonth; day++) {
                    const selectedClass = day === selectedHijriDay ? 'selected' : '';
                    html += `<div class="calendar-day ${selectedClass}" data-day="${day}">${day}</div>`;
                }

                hijriCalendarDays.innerHTML = html;

                hijriCalendarDays.querySelectorAll('.calendar-day:not(.disabled)').forEach(dayEl => {
                    dayEl.addEventListener('click', function() {
                        selectedHijriDay = parseInt(this.getAttribute('data-day'));
                        hijriDayInput.value = selectedHijriDay;
                        hijriCalendar.classList.remove('active');
                        generateHijriCalendar();
                    });
                });
            }

            hijriDayInput.addEventListener('click', function() {
                hijriCalendar.classList.toggle('active');
                generateHijriCalendar();
            });

            hijriMonth.addEventListener('change', function() {
                generateHijriCalendar();
            });

            // Convert date function
            convertBtn.addEventListener('click', async function() {
                loader.classList.add('active');
                resultContainer.classList.remove('active');

                try {
                    let result;

                    if (isGregorianToHijri) {
                        result = await convertGregorianToHijri();
                        resultTitle.textContent = 'Gregorian to Hijri Conversion';
                        resultDate.textContent = result.hijriDate;
                        resultDetails.textContent = `${result.gregorianDate} = ${result.hijriDate}`;
                    } else {
                        result = await convertHijriToGregorian();
                        resultTitle.textContent = 'Hijri to Gregorian Conversion';
                        resultDate.textContent = result.gregorianDate;
                        resultDetails.textContent = `${result.hijriDate} = ${result.gregorianDate}`;
                    }

                    resultContainer.classList.add('active');

                } catch (error) {
                    alert('Error converting date: ' + error.message);
                } finally {
                    loader.classList.remove('active');
                }
            });

            // Convert Gregorian to Hijri
            async function convertGregorianToHijri() {
                const year = gregorianYear.value;
                const month = String(gregorianMonth.value).padStart(2, '0');
                const day = String(selectedGregorianDay).padStart(2, '0');

                const formattedDate = `${day}-${month}-${year}`;
                const apiUrl = `https://api.aladhan.com/v1/gToH/${formattedDate}`;

                const response = await fetch(apiUrl);
                const data = await response.json();

                if (data.code === 200) {
                    const hijri = data.data.hijri;
                    const gregorian = data.data.gregorian;

                    return {
                        hijriDate: `${hijri.day} ${hijri.month.en} ${hijri.year}`,
                        gregorianDate: `${gregorian.day} ${gregorian.month.en} ${gregorian.year}`
                    };
                } else {
                    throw new Error('Conversion failed');
                }
            }

            // Convert Hijri to Gregorian
            async function convertHijriToGregorian() {
                const year = hijriYear.value;
                const month = String(hijriMonth.value).padStart(2, '0');
                const day = String(selectedHijriDay).padStart(2, '0');

                const formattedDate = `${day}-${month}-${year}`;
                const apiUrl = `https://api.aladhan.com/v1/hToG/${formattedDate}`;

                const response = await fetch(apiUrl);
                const data = await response.json();

                if (data.code === 200) {
                    const hijri = data.data.hijri;
                    const gregorian = data.data.gregorian;

                    return {
                        gregorianDate: `${gregorian.day} ${gregorian.month.en} ${gregorian.year}`,
                        hijriDate: `${hijri.day} ${hijri.month.en} ${hijri.year}`
                    };
                } else {
                    throw new Error('Conversion failed');
                }
            }

            // Initialize calendars
            generateGregorianCalendar();
            generateHijriCalendar();

            // Close calendar when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.calendar-container')) {
                    gregorianCalendar.classList.remove('active');
                    hijriCalendar.classList.remove('active');
                }
            });
        </script>
<?php
        return ob_get_clean();
    }
}

// Initialize the shortcode
new DateConversion();
