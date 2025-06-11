<?php
/*
* Plugin Name: Keto Macro Calculator
* Plugin URI:        https://jamesdennis.org
* Description: Keto Macro Calculator is a simple calculator, which is used to measure nutritional needs for a ketogenic diet. Use shortcode "[[keto_macro_calculator]]" anywhere on your page.
* Version: 1.0
* Author: James Dennis
* Author URI: https://jamesdennis.org
* Text Domain: keto-calculator
* License: GPL v3.0
*/

function keto_macro_calculator_init() {
    $plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages'; 
    load_plugin_textdomain( 'keto_macro_calculator', false, $plugin_rel_path );
}
add_action('plugins_loaded', 'keto_macro_calculator_init');

// Enqueue the JavaScript and CSS files
function keto_macro_calculator_enqueue_scripts() {
    // Versioning for cache-busting
    $plugin_version = '1.6'; 
    
    // Enqueue the ApexCharts JavaScript library
    wp_enqueue_script(
        'apexcharts',
        'https://cdn.jsdelivr.net/npm/apexcharts',
        array(),
        '3.35.0', 
        true
    );

    // Enqueue the main JavaScript file
    wp_enqueue_script(
        'keto-calculator-js',
        plugin_dir_url(__FILE__) . 'assets/js/keto-calculator.js',
        array('jquery'),
        $plugin_version, 
        true
    );
    wp_enqueue_style(
        'keto-calculator-style',
        plugin_dir_url(__FILE__) . 'assets/css/keto-calculator.css',
        array(),
        $plugin_version,
    );
}
add_action('wp_enqueue_scripts', 'keto_macro_calculator_enqueue_scripts');


// Register the Keto Calculator Shortcode
function keto_macro_calculator() {
    ob_start();  
    ?>
    <div id="ketoCalculator">
        <div class="calc-form-wrapper">
            <form id="ketoForm" class="keto-calculator-modern">
                <div class="form-section">
                    <div class="form-group unit radio-group">
                        <label class="form-label"><?php esc_html_e('Unit System:', 'keto-calculator'); ?></label>
                        <div class="radio-options">
                            <label class="radio-option">
                                <input type="radio" name="unitSystem" value="metric" checked />
                                <span class="radio-icon"></span>
                                <span class="radio-label"><?php esc_html_e('Metric', 'keto-calculator'); ?></span>
                                <span class="dashicons dashicons-admin-site-alt3"></span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="unitSystem" value="us_customary" />
                                <span class="radio-icon"></span>
                                <span class="radio-label"><?php esc_html_e('Imperial', 'keto-calculator'); ?></span>
                                <span class="dashicons dashicons-admin-site"></span>
                            </label>
                        </div>
                    </div>
                    <h3 class="form-section-title"><?php esc_html_e('Personal Details', 'keto-calculator'); ?></h3>

                    <div class="form-row">
                        <div class="form-group gender">
                            <label for="gender" class="form-label">
                                <span class="dashicons dashicons-admin-users"></span>
                                <?php esc_html_e('Gender:', 'keto-calculator'); ?>
                            </label>
                            <div class="select-wrapper">
                                <select id="gender" name="gender">
                                    <option value="male"><?php esc_html_e('Male', 'keto-calculator'); ?></option>
                                    <option value="female"><?php esc_html_e('Female', 'keto-calculator'); ?></option>
                                </select>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </div>
                        </div>

                        <div class="form-group age">
                            <label for="age" class="form-label">
                                <span class="dashicons dashicons-calendar"></span>
                                <?php esc_html_e('Age:', 'keto-calculator'); ?>
                            </label>
                            <input type="number" id="age" name="age" placeholder="30">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group weight">
                            <label for="weight" class="form-label">
                                <span class="dashicons dashicons-performance"></span>
                                <?php esc_html_e('Weight:', 'keto-calculator'); ?><span class="unit" id="weightUnit">kg</span>
                            </label>
                            <div class="input-with-unit">
                                <input type="number" id="weight" name="weight" placeholder="70">
                            </div>
                        </div>

                        <div class="form-group height">
                            <label for="height" class="form-label">
                                <span class="dashicons dashicons-arrow-up-alt"></span>
                                <?php esc_html_e('Height:', 'keto-calculator'); ?><span class="unit" id="heightUnit">cm</span>
                            </label>
                            <div class="input-with-unit">
                                <input type="number" id="height" name="height" placeholder="175">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><?php esc_html_e('Activity & Goals', 'keto-calculator'); ?></h3>
                    
                    <div class="form-group activity">
                        <label for="activity_level" class="form-label">
                            <span class="dashicons dashicons-universal-access-alt"></span>
                            <?php esc_html_e('Activity Level:', 'keto-calculator'); ?>
                        </label>
                        <div class="select-wrapper">
                            <select id="activity_level" name="activity_level">
                                <option value="sedentary"><?php esc_html_e('Sedentary', 'keto-calculator'); ?></option>
                                <option value="light"><?php esc_html_e('Lightly Active', 'keto-calculator'); ?></option>
                                <option value="active"><?php esc_html_e('Active', 'keto-calculator'); ?></option>
                                <option value="very_active"><?php esc_html_e('Very Active', 'keto-calculator'); ?></option>
                            </select>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group body-fat">
                        <label for="bodyFat" class="form-label">
                            <span class="dashicons dashicons-chart-area"></span>
                            <?php esc_html_e('Body Fat: %', 'keto-calculator'); ?>
                        </label>
                        <div class="input-with-unit">
                            <input type="number" id="bodyFat" name="bodyFat" placeholder="20">
                        </div>
                        </div>

                        <div class="form-group goal">
                            <label for="goal" class="form-label">
                                <span class="dashicons dashicons-marker"></span>
                                <?php esc_html_e('Goal:', 'keto-calculator'); ?>
                            </label>
                            <div class="select-wrapper">
                                <select id="goal" name="goal">
                                    <option value="lose"><?php esc_html_e('Lose Weight', 'keto-calculator'); ?></option>
                                    <option value="gain"><?php esc_html_e('Gain Weight', 'keto-calculator'); ?></option>
                                </select>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </div>
                        </div>
                    </div>
                    
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><?php esc_html_e('Keto Settings', 'keto-calculator'); ?></h3>
                    
                    <div class="form-group carbs">
                        <label for="net_carbs" class="form-label">
                            <span class="dashicons dashicons-carrot"></span>
                            <?php esc_html_e('Daily Net Carbs:', 'keto-calculator'); ?><span class="unit">% of Calories</span>
                        </label>
                        <div class="input-with-unit">
                            <input type="number" id="net_carbs" name="net_carbs" step="0.1" min="5" max="10" placeholder="5 - 10">
                            
                        </div>
                        <p class="form-description">
                            <?php esc_html_e('Specify your target daily net carbs (typically 5 - 10 % of daily Calories or 20-30g/day to start ketosis)', 'keto-calculator'); ?>
                        </p>
                    </div>
                </div>

                <button type="submit" class="keto-calc-btn">
                    <span class="dashicons dashicons-calculator"></span>
                    <?php esc_html_e('Calculate My Macros', 'keto-calculator'); ?>
                </button>
            </form>
            <div class="calc-side-panel">
                <strong>Gender</strong>
                <p>Male/Female metbolisms differ and so, daily calorific needs, especially on the Ketogeic diet, should reflect this.</p>
                <strong>Age Weight Height</strong>
                <p>Calculating your basal metabolic rate (BMR) is crucial to tailoring accurate results. These factors play significant roles in determining the energy your body needs.</p>
                <strong>Age:</strong>
                <p>Metabolism naturally changes with age. This shift can impact the amount of energy our body requires to maintain basic functions.</p>
                <strong>Weight:</strong>
                <p>Your body weight directly influences the energy necessary to sustain bodily processes. A heavier body typically demands more energy to maintain itself.</p>
                <strong>Height:</strong>
                <p>Height contributes to your BMR calculation because taller individuals tend to have larger body surfaces, which results in increased heat loss and energy expenditure.</p>
                <strong>Activity Level</strong>
                <p>Your BMR represents the calories burned during rest and digestion, combining this with activity, gives your TDEE (total daily energy expenditure) – the calculator’s daily calorie estimate.</p>
                <strong>Body Fat</strong>
                <p>The keto calculator employs body fat percentage to determine lean body mass, enabling accurate protein calculation for weight loss without muscle loss.</p>
                <p>Balancing protein intake is crucial, as insufficient or excessive amounts can yield undesirable outcomes in a ketogenic or any diet.</p>
            </div>
        </div>
            
        <div id="ketoResults" style="visibility: hidden; opacity: 0">
            <div class="main-results">
                <div class="results-group">
                    <h2><?php esc_html_e('Your Results', 'keto-calculator'); ?></h2>
                    <div class="result-group">
                        <p class="value"><?php esc_html_e('BMR:', 'keto-calculator'); ?> <span id="bmrValue">--</span> kcal/day</p>
                        <p>Your Current Base Metabolic Rate: Daily Calories for your Age, Gender, Weight and Height.</p>
                    </div>
                    <div class="result-group">
                        <p class="value"><?php esc_html_e('Calories to Consume:', 'keto-calculator'); ?> <span id="caloriesToConsume">--</span> kcal/day</p>
                        <p>Your Target Calorie Intake for a Standard Ketogenic Diet..</p>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <div id="chart"></div>
                    <div class="chart-summary">
                        <p>
                            <?php 
                            echo esc_html__('A', 'keto-calculator') . ' '; 
                            ?><span id="summaryAge">--</span><?php 
                            echo ' ' . esc_html__('year old', 'keto-calculator') . ' '; 
                            ?><span id="summaryActivity">--</span><?php 
                            echo ' '; 
                            ?><span id="summaryGender">--</span><?php 
                            echo ', '; 
                            ?><span id="summaryHeight">--</span><?php 
                            echo ' ' . esc_html__('tall', 'keto-calculator') . ', ' . esc_html__('weighing', 'keto-calculator') . ' '; 
                            ?><span id="summaryWeight">--</span><?php 
                            echo ', ' . esc_html__('with a body fat % of', 'keto-calculator') . ' '; 
                            ?><span id="summaryBodyFat">--</span><?php 
                            echo '.'; 
                            ?>
                        </p>
                    </div>
                </div>
                
            </div>
            
            <div class="variations-header">
                <h3>Weight Target Variations</h3>
                <p>For faster weight target achievement, consider these variations...</p>
            </div>
            
            <!-- Deficit Charts -->
            <div class="chart-slider-wrapper" id="calorieDeficitCharts" style="display: none;">
                <button class="slider-nav prev"><strong>&larr;</strong></button>
                
                <div class="chart-slider-container">
                    <div class="chart-slider">
                        <!-- Charts Injected Here by Javascript -->
                    </div>
                </div>

                <button class="slider-nav next"><strong>&rarr;</strong></button>
                <!-- Dots navigation -->
                <div class="slider-dots"></div>
            </div>
            
            <!-- Surplus Charts -->
            <div class="chart-slider-wrapper" id="calorieSurplusChart" style="display: none;">
                <button class="slider-nav prev"><strong>&larr;</strong></button>
                

                <div class="chart-slider-container">
                    <div class="chart-slider">
                    <!-- Charts Injected Here by Javascript -->
                    </div>
                </div>
                
                <button class="slider-nav next"><strong>&rarr;</strong></button>
                <!-- Dots navigation -->
                <div class="slider-dots"></div>
            </div>
        </div>    
    </div>
        
            

    <?php
    return ob_get_clean();  
}
add_shortcode('keto_macro_calculator', 'keto_macro_calculator');
