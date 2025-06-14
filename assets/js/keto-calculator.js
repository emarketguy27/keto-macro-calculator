document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('ketoForm');
    let mainChart = null;
    let deficitCharts = [];
    let surplusCharts = [];

    // Generate Macro Results Handler
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        
        const unitSystem = document.querySelector('input[name="unitSystem"]:checked').value;
        
        const formValues = {
            gender: document.getElementById('gender').value,
            age: parseInt(document.getElementById('age').value),
            weight: parseFloat(document.getElementById('weight').value),
            height: parseFloat(document.getElementById('height').value),
            bodyFat: parseFloat(document.getElementById('bodyFat').value),
            activityLevel: document.getElementById('activity_level').value,
            goal: document.getElementById('goal').value,
            carbsChoice: parseFloat((document.getElementById('net_carbs').value)) || 7.5,
            unitSystem: unitSystem
        };

        // Validate inputs
        if (isNaN(formValues.age) || isNaN(formValues.weight) || isNaN(formValues.height) || 
            isNaN(formValues.bodyFat) || isNaN(formValues.carbsChoice)) {
            alert('Please fill in all required fields with valid numbers');
            return;
        }

        // Convert units if needed
        if (formValues.unitSystem === 'us_customary') {
            formValues.weight = convertWeightToKg(formValues.weight);
            formValues.height = convertHeightToCm(formValues.height);
        }

        calculateResults(formValues); // Pass the values object

        console.log('Form values:', formValues); // Debug

        document.getElementById('summaryAge').textContent = document.getElementById('age').value;
        document.getElementById('summaryActivity').textContent = document.getElementById('activity_level').options[document.getElementById('activity_level').selectedIndex].text;
        document.getElementById('summaryGender').textContent = document.getElementById('gender').value === 'male' ? 'male' : 'female';
        document.getElementById('summaryHeight').textContent = document.getElementById('height').value + ' ' + document.getElementById('heightUnit').textContent;
        document.getElementById('summaryWeight').textContent = document.getElementById('weight').value + ' ' + document.getElementById('weightUnit').textContent;
        document.getElementById('summaryBodyFat').textContent = document.getElementById('bodyFat').value;

        setTimeout(() => {
            const resultsSection = document.getElementById('ketoResults');
            
            if (resultsSection) {
                resultsSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start' // Aligns to top of element
                });
                resultsSection.style = "visibility: visible; opacity: 1; height: 100%";
                // Optional: Add focus for better accessibility
                resultsSection.setAttribute('tabindex', '-1');
                resultsSection.focus();
            }
        }, 300); // Small delay to ensure DOM updates
    });

    function debugChartRendering() {
        // Check if container exists and has dimensions
        const chartEl = document.getElementById('chart');
        if (!chartEl) {
            console.error('Chart container not found');
            return false;
        }
        
        const rect = chartEl.getBoundingClientRect();
        if (rect.width === 20 || rect.height === 20) {
            console.warn('Chart container has zero dimensions', rect);
            return false;
        }
        
        // Check ApexCharts version
        if (typeof ApexCharts === 'undefined') {
            console.error('ApexCharts not loaded');
            return false;
        }
        
        console.log('Chart rendering environment OK');
        return true;
    }

    // Call this before initializing any charts
    if (!debugChartRendering()) {
        console.warn('Chart rendering conditions not met - delaying initialization');
        setTimeout(() => {
            if (debugChartRendering()) {
                initializeCharts();
            }
        }, 500);
    }

    function convertWeightToKg(weightInLb) {
        return weightInLb * 0.453592; // 1 lb = 0.453592 kg
    }

    function convertHeightToCm(heightInInches) {
        return heightInInches * 2.54; // 1 inch = 2.54 cm
    }

    function calculateTDEEFromForm(formValues) {
        // Fallback to empty object if undefined
        const { 
            gender = 'male', 
            age = 30, 
            weight = 70, 
            height = 175, 
            bodyFat, 
            activityLevel = 'sedentary' 
        } = formValues || {};
        
        // Your exact BMR calculation from calculateResults()
        let bmr;
        if (bodyFat && !isNaN(bodyFat)) {
            const leanMass = weight * (1 - (bodyFat / 100));
            bmr = 370 + (21.6 * leanMass);
        } else {
            bmr = (gender === "male") 
            ? 10 * weight + 6.25 * height - 5 * age + 5 
            : 10 * weight + 6.25 * height - 5 * age - 161;
        }
        
        // Your exact activity multipliers
        const activityMultipliers = {
            sedentary: 1.2,
            light: 1.375,
            active: 1.55,
            very_active: 1.725
        };
        
        return bmr * (activityMultipliers[activityLevel] || 1.2);
    }

    function calculateResults(formValues) {
        const { gender, age, weight, height, bodyFat, activityLevel, goal, carbsChoice } = formValues;
        // 1. Calculate BMR
        let bmr;
        if (bodyFat && !isNaN(bodyFat)) {
            const leanMass = weight * (1 - (bodyFat / 100));
            bmr = 370 + (21.6 * leanMass);
        } else {
            bmr = (gender === "male") 
                ? 10 * weight + 6.25 * height - 5 * age + 5 
                : 10 * weight + 6.25 * height - 5 * age - 161;
        }
        
        let bmrCalories = bmr;

        // 2. Calculate TDEE
        const activityMultipliers = {
            sedentary: 1.2,
            light: 1.375,
            active: 1.55,
            very_active: 1.725
        };

        let tdee = bmr * activityMultipliers[activityLevel];
        
        // 3. FIXED MACRO CALCULATIONS
        let carbCalories = tdee * (carbsChoice / 100);
        let proteinCalories = tdee * 0.15;
        let fatCalories = tdee - (carbCalories + proteinCalories);

        let ketoCalories = bmrCalories * activityMultipliers[activityLevel];
        let proteinPercent = (proteinCalories / ketoCalories) *100;
        let fatPercent = (fatCalories / ketoCalories) * 100;
        let carbPercent = (carbsChoice);

        let displayCalories = tdee;
        
        // Debug
        console.log("Fat%, Protein%, Carbs% = " + fatPercent, proteinPercent, carbPercent);
        console.log("Base Maintenance Calories: " + displayCalories);

        // 4. Deficit / Surplus Logic
        let deficit = {};
        if (goal === "lose") {
            deficit = {
                small: tdee * 0.9,
                moderate: tdee * 0.8,
                large: tdee * 0.7,
                displayCalories: deficit.moderate
            };
            // calories = deficit.moderate;
        } else if (goal === "gain") {
            ketoCalories += 500;
        }

        

        // 5. Update DOM - "Your Results"
        document.getElementById('bmrValue').textContent = Math.round(bmr);
        document.getElementById('caloriesToConsume').textContent = Math.round(displayCalories);

        // Main Chart
        const chartPercentages = [
            (fatCalories / tdee) * 100,
            (proteinCalories / tdee) * 100,
            Number(carbsChoice)
        ];
        
        updateChart(chartPercentages, fatCalories, proteinCalories, carbCalories);
        // toggleChartDisplay(goal, deficit, (goal === "gain") ? ketoCalories : null, formValues);
        toggleChartDisplay(goal, deficit, formValues, tdee);
    }

    function updateChart(chartPercentages, fatCalories, proteinCalories, carbCalories) {
        const roundedFatCalories = Math.round(fatCalories);
        const roundedProteinCalories = Math.round(proteinCalories);
        const roundedCarbCalories = Math.round(carbCalories);

        chartLabels = [
            `Fat: ${roundedFatCalories} kCal`,
            `Protein: ${roundedProteinCalories} kCal`,
            `Carbs: ${roundedCarbCalories} kCal`
        ];

        const chartOptions = {
            chart: {
                width: 500,
                type: 'pie',
                animations: {
                    enabled: true,
                    easing: 'easeout',
                    speed: 800
                },
                dropShadow: {
                    enabled: true,
                    top: 0,
                    left: 0,
                    blur: 6,
                    color: '#000',
                    opacity: 0.4
                },
                redrawOnParentResize: true,
                redrawOnWindowResize: true
            },
            legend: {
                position: 'bottom'
            },
            series: chartPercentages,
            labels: chartLabels,
            colors: ["#FF6384", "#36A2EB", "#FFCE56"],
            tooltip: {
                enabled: true,
                y: {
                    formatter: function(value) {
                        return `${Math.round(value)}%`;
                    }
                }
            },
            responsive: [{
                breakpoint: 768,
                options: {
                    chart: {
                        width: 240,
                        height: 450
                    },
                    legend: {
                        position: 'bottom',
                        top: 310,
                        horizontalAlign: 'center',
                        onItemHover: {
                            highlightDataSeries: true,
                        }
                    }
                }
            }]
        };

        if (!mainChart) {
            setTimeout(() => {
                try {
                    mainChart = new ApexCharts(document.getElementById("chart"), chartOptions);
                    mainChart.render();
                } catch (error) {
                    console.error('Chart initialization failed:', error);
                }
            }, 50);
        } else {
            mainChart.updateOptions(chartOptions);
        }
    }

    function toggleChartDisplay(goal, deficit, formValues, tdee) {

        const deficitContainer = document.getElementById('calorieDeficitCharts');
        const surplusContainer = document.getElementById('calorieSurplusChart');
        
        if (goal === "lose") {
            deficitContainer.style.display = 'flex';
            surplusContainer.style.display = 'none';
            updateDeficitCharts(deficit, formValues);
        } else if (goal === "gain") {
            deficitContainer.style.display = 'none'; // Now shows 3 charts
            surplusContainer.style.display = 'flex';

            const surplus = {
                small: tdee + 250,
                moderate: tdee + 500,
                large: tdee + 750
            };
            updateSurplusChart(surplus, formValues);
        } else {
            deficitContainer.style.display = 'none';
            surplusContainer.style.display = 'none';
        }
    }

    function updateDeficitCharts(deficit, formValues) {
        // Clear previous charts
        if (deficitCharts.length) {
            deficitCharts.forEach(chart => chart.destroy());
            deficitCharts = [];
        }

        // 2. Get slider elements
        const sliderContainer = document.getElementById('calorieDeficitCharts');
        const sliderTrack = sliderContainer.querySelector('.chart-slider');

        // 3. Verify elements exist
        if (!sliderContainer || !sliderTrack) {
            console.error('Slider elements not found!');
            return;
        }

        // 4. Clear slider track
        sliderTrack.innerHTML = '';

        // Macro ratios (70% fat, 17.5% protein, remaining carbs)
        const carbGrams = formValues?.carbPercent || 12.5; // Default carbs if not provided
        const fatRatio = 0.7;  // 70% of calories from fat
        const proteinRatio = 0.175; // 17.5% from protein

        // Helper function to calculate grams + calories for a given deficit level
        const getMacros = (calories) => {
            const fatGrams = Math.round((calories * fatRatio) / 9);
            const proteinGrams = Math.round((calories * proteinRatio) / 4);
            const fatCalories = Math.round(fatGrams * 9);
            const proteinCalories = Math.round(proteinGrams * 4);
            const carbCalories = Math.round(carbGrams * 4);

            return {
                fat: { grams: fatGrams, calories: fatCalories },
                protein: { grams: proteinGrams, calories: proteinCalories },
                carbs: { grams: carbGrams, calories: carbCalories },
                totalCalories: Math.round(calories) // Total calories for the deficit level
            };
        };


        // Calculate macros for each deficit level
        const deficits = [
            { title: 'Small Deficit (10%)', calories: deficit.small },
            { title: 'Moderate Deficit (20%)', calories: deficit.moderate },
            { title: 'Large Deficit (30%)', calories: deficit.large }
        ];

        // Generate charts
        deficits.forEach(def => {
            const macros = getMacros(def.calories);

            const slide = document.createElement('div');
            slide.className = 'deficit-chart-slide';
            sliderTrack.appendChild(slide);

            // Add title
            const titleEl = document.createElement('h3');
            titleEl.textContent = `${def.title} • ${macros.totalCalories} kcal`;
            slide.appendChild(titleEl);

            // Render the chart with timeout delay
            setTimeout(() => {
                try {
                    const chart = new ApexCharts(slide, {
                    chart: { type: 'pie', height: 400, width: '100%', animations: { enabled: true } },
                    series: [macros.fat.grams, macros.protein.grams, macros.carbs.grams],
                    labels: [
                        `Fat: ${macros.fat.grams}g (${macros.fat.calories} kcal)`,
                        `Protein: ${macros.protein.grams}g (${macros.protein.calories} kcal)`,
                        `Carbs: ${macros.carbs.grams}g (${macros.carbs.calories} kcal)`
                    ],
                    colors: ["#FF6384", "#36A2EB", "#FFCE56"],
                    tooltip: { y: { formatter: (val) => `${val}g` } }
                });
                chart.render();
                deficitCharts.push(chart);
                } catch (error) {
                    console.error('Chart rendering failed:', error);
                }
            }, 50);
        });

        // createDots(document.getElementById('calorieDeficitCharts'));
        initSliderDots(document.getElementById('calorieDeficitCharts'));
    }
    
    function updateSurplusChart(surplus, formValues) {
        // Clear previous charts
        surplusCharts = surplusCharts || [];
        surplusCharts.forEach(chart => {
            try { chart.destroy(); } catch(e) { console.warn(e); }
        });
        surplusCharts = [];

        const container = document.getElementById('calorieSurplusChart');
        const sliderTrack = container.querySelector('.chart-slider');
        sliderTrack.innerHTML = '';

        // Macro ratios (same as deficit)
        const carbGrams = formValues?.carbPercent || 12.5;
        const fatRatio = 0.7;
        const proteinRatio = 0.175;

        // Helper function (identical to deficit version)
        const getMacros = (calories) => {
            const fatGrams = Math.round((calories * fatRatio) / 9);
            const proteinGrams = Math.round((calories * proteinRatio) / 4);
            const fatCalories = Math.round(fatGrams * 9);
            const proteinCalories = Math.round(proteinGrams * 4);
            const carbCalories = Math.round(carbGrams * 4);

            return {
                fat: { grams: fatGrams, calories: fatCalories },
                protein: { grams: proteinGrams, calories: proteinCalories },
                carbs: { grams: carbGrams, calories: carbCalories },
                totalCalories: Math.round(calories)
            };
        };

        // Create three surplus levels (parallel to deficit structure)
        const surplusLevels = [
            { title: 'Small Surplus (+250 kcal)', calories: surplus.small },
            { title: 'Moderate Surplus (+500 kcal)', calories: surplus.moderate },
            { title: 'Large Surplus (+750 kcal)', calories: surplus.large }
        ];

        // Generate charts (identical to deficit approach)
        surplusLevels.forEach(level => {
            const macros = getMacros(level.calories);

            const slide = document.createElement('div');
            slide.className = 'surplus-chart-slide';
            sliderTrack.appendChild(slide);

            // Add title (FIXED: was using chart.title which doesn't exist)
            const titleEl = document.createElement('h3');
            titleEl.textContent = `${level.title} • ${macros.totalCalories} kcal`;
            slide.appendChild(titleEl);

            // Render chart with identical config as deficit
            setTimeout(() => {
                try {
                    const chart = new ApexCharts(slide, {
                        chart: { 
                            type: 'pie', 
                            height: 400, 
                            width: '100%', 
                            animations: { enabled: true } 
                        },
                        series: [
                            macros.fat.grams, 
                            macros.protein.grams, 
                            macros.carbs.grams
                        ],
                        labels: [
                            `Fat: ${macros.fat.grams}g (${macros.fat.calories} kcal)`,
                            `Protein: ${macros.protein.grams}g (${macros.protein.calories} kcal)`,
                            `Carbs: ${macros.carbs.grams}g (${macros.carbs.calories} kcal)`
                        ],
                        colors: ["#FF6384", "#36A2EB", "#FFCE56"],
                        tooltip: { 
                            y: { formatter: (val) => `${val}g` } 
                        }
                    });
                    chart.render();
                    surplusCharts.push(chart);
                } catch (error) {
                    console.error('Chart rendering failed:', error);
                }
            }, 50);
        });

        // Initialize dots (FIXED: typo in ID was 'calorieSurplusCharts')
        initSliderDots(document.getElementById('calorieSurplusChart'));
    }
    
    // Slider Button click handlers
    document.querySelectorAll('.slider-nav').forEach(button => {
        button.addEventListener('click', (e) => {
            const wrapper = button.closest('.chart-slider-wrapper');
            const container = wrapper.querySelector('.chart-slider-container');
            const scrollAmount = container.clientWidth * 0.8; // Adjust as needed
            
            container.scrollBy({
            left: button.classList.contains('prev') ? -scrollAmount : scrollAmount,
            behavior: 'smooth'
            });

            setTimeout(() => {
            button.closest('.chart-slider-wrapper')
                    .querySelector('.slider-dots')
                    .dispatchEvent(new Event('scroll'));
            }, 300);
        });
    });

    // Initialize for all sliders
    function initSliderDots(sliderWrapper) {
    const container = sliderWrapper.querySelector('.chart-slider-container');
    const slider = sliderWrapper.querySelector('.chart-slider');
    const dotsContainer = sliderWrapper.querySelector('.slider-dots');
    const slides = slider.children;
    
    // Clear and recreate dots
    dotsContainer.innerHTML = '';
    Array.from(slides).forEach((_, index) => {
        const dot = document.createElement('div');
        dot.className = 'slider-dot';
        if (index === 0) dot.classList.add('active');
        
        dot.addEventListener('click', () => {
        goToSlide(index);
        });
        
        dotsContainer.appendChild(dot);
    });

    // Sync dots with scroll position
    container.addEventListener('scroll', updateActiveDot);
    
    function goToSlide(index) {
        container.scrollTo({
        left: slides[index].offsetLeft,
        behavior: 'smooth'
        });
    }

    function updateActiveDot() {
        const scrollPos = container.scrollLeft + (container.clientWidth / 2);
        
        Array.from(slides).forEach((slide, index) => {
        const dot = dotsContainer.children[index];
        const slideStart = slide.offsetLeft;
        const slideEnd = slideStart + slide.offsetWidth;
        
        dot.classList.toggle('active', scrollPos >= slideStart && scrollPos <= slideEnd);
        });
    }
    
    // Update dots when arrows are clicked
    sliderWrapper.querySelectorAll('.slider-nav').forEach(btn => {
        btn.addEventListener('click', () => {
        setTimeout(updateActiveDot, 300); // Match scroll duration
        });
    });
    }
});

jQuery(document).ready(function($) {
    function updateUnits() {
        var unitSystem = $('input[name="unitSystem"]:checked').val();
        console.log("Selected unit system:", unitSystem); // Debugging

        if (unitSystem === 'metric') {
            $('#weightUnit').text('kg');
            $('#heightUnit').text('cm');
        } else if (unitSystem === 'us_customary') {
            $('#weightUnit').text('lbs');
            $('#heightUnit').text('inches');
        }
    }

    updateUnits(); // Initialize on load

    // scroll charts into view
    $('input[name="unitSystem"]').on('change', updateUnits); // Update on change

    $('#ketoForm').on('submit', function(e) {
        e.preventDefault();    
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#ketoResults').offset().top - 20
        }, 800);
    });
});

// ***** Debug check to test loading of charts CDN turned Off unless debugging chart issues ***** //
// if (typeof ApexCharts === 'undefined') {
//     console.error('ApexCharts is not loaded!');
// } else {
//     console.log('ApexCharts loaded successfully', ApexCharts);
// }
