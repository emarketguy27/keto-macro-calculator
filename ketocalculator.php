<?php
/*
* Plugin Name: Keto Macro Calculator
* Plugin URI: https://jamesdennis.org/plugins/keto-macro-calculator
* Description: A modern, accurate nutrition calculator used to measure nutritional needs for a ketogenic diet.
* Version: 1.5
* Requires at least: 5.6
* Author: James Dennis
* Author URI: https://jamesdennis.org
* License: GPL v3.0
* License URI: https://www.gnu.org/licenses/gpl-3.0.html
* Text Domain: keto-macro-calculator
*/

function keto_macro_calculator_init() {
    $plugin_rel_path = basename( dirname( __FILE__ ) ) . '/languages'; 
    load_plugin_textdomain( 'keto_macro_calculator', false, $plugin_rel_path );
}
add_action('plugins_loaded', 'keto_macro_calculator_init');

// Enqueue the JavaScript and CSS files
function keto_macro_calculator_enqueue_scripts() {
	global $post;
	
	// Check if current post contains our shortcode
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'keto_macro_calculator')) {
        // ApexCharts JavaScript library
		wp_enqueue_script(
			'apexcharts-js',
			plugin_dir_url(__FILE__) . 'assets/js/apexcharts.min.js',
            array(),
            '3.35.0',
            true
		);

		// main JavaScript file
		wp_enqueue_script(
			'keto-calculator-js',
			plugin_dir_url(__FILE__) . 'assets/js/keto-calculator.min.js',
			array('jquery'),
			'1.5', 
			true
		);
        // main JavaScript file
		wp_enqueue_style(
			'keto-calculator-style',
			plugin_dir_url(__FILE__) . 'assets/css/keto-calculator.min.css',
			array(),
			'1.5',
		);
        // For frontend display (if using icons in shortcode/output)
        add_action('wp_enqueue_scripts', function() {
            wp_enqueue_style('dashicons');
        });

        // For admin pages (if using icons in admin UI)
        add_action('admin_enqueue_scripts', function() {
            wp_enqueue_style('dashicons');
        });
    } 
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
                        <label class="form-label"><?php esc_html_e('Unit System:', 'keto-macro-calculator'); ?></label>
                        <div class="radio-options">
                            <label class="radio-option">
                                <input type="radio" name="unitSystem" value="metric" checked />
                                <span class="radio-icon"></span>
                                <span class="radio-label"><?php esc_html_e('Metric', 'keto-macro-calculator'); ?></span>
                                <span class="dashicons dashicons-admin-site-alt3"></span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="unitSystem" value="us_customary" />
                                <span class="radio-icon"></span>
                                <span class="radio-label"><?php esc_html_e('Imperial', 'keto-macro-calculator'); ?></span>
                                <span class="dashicons dashicons-admin-site"></span>
                            </label>
                        </div>
                    </div>
                    <h3 class="form-section-title"><?php esc_html_e('Personal Details', 'keto-macro-calculator'); ?></h3>

                    <div class="form-row">
                        <div class="form-group gender">
                            <label for="gender" class="form-label">
                                <span class="dashicons dashicons-admin-users"></span>
                                <?php esc_html_e('Gender:', 'keto-macro-calculator'); ?>
                            </label>
                            <div class="select-wrapper">
                                <select id="gender" name="gender">
                                    <option value="male"><?php esc_html_e('Male', 'keto-macro-calculator'); ?></option>
                                    <option value="female"><?php esc_html_e('Female', 'keto-macro-calculator'); ?></option>
                                </select>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </div>
                        </div>

                        <div class="form-group age">
                            <label for="age" class="form-label">
                                <span class="dashicons dashicons-calendar"></span>
                                <?php esc_html_e('Age:', 'keto-macro-calculator'); ?>
                            </label>
                            <div class="input-with-unit">
                                <input type="number" id="age" name="age" placeholder="30">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group weight">
                            <label for="weight" class="form-label">
                                <span class="dashicons dashicons-performance"></span>
                                <?php esc_html_e('Weight:', 'keto-macro-calculator'); ?><span class="unit" id="weightUnit">kg</span>
                            </label>
                            <div class="input-with-unit">
                                <input type="number" id="weight" name="weight" placeholder="70">
                            </div>
                        </div>

                        <div class="form-group height">
                            <label for="height" class="form-label">
                                <span class="dashicons dashicons-arrow-up-alt"></span>
                                <?php esc_html_e('Height:', 'keto-macro-calculator'); ?><span class="unit" id="heightUnit">cm</span>
                            </label>
                            <div class="input-with-unit">
                                <input type="number" id="height" name="height" placeholder="175">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><?php esc_html_e('Activity & Goals', 'keto-macro-calculator'); ?></h3>
                    
                    <div class="form-group activity">
                        <label for="activity_level" class="form-label">
                            <span class="dashicons dashicons-universal-access-alt"></span>
                            <?php esc_html_e('Activity Level:', 'keto-macro-calculator'); ?>
                        </label>
                        <div class="select-wrapper">
                            <select id="activity_level" name="activity_level">
                                <option value="sedentary"><?php esc_html_e('Sedentary', 'keto-macro-calculator'); ?></option>
                                <option value="light"><?php esc_html_e('Lightly Active', 'keto-macro-calculator'); ?></option>
                                <option value="active"><?php esc_html_e('Active', 'keto-macro-calculator'); ?></option>
                                <option value="very_active"><?php esc_html_e('Very Active', 'keto-macro-calculator'); ?></option>
                            </select>
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group body-fat">
                        <label for="bodyFat" class="form-label">
                            <span class="dashicons dashicons-chart-area"></span>
                            <?php esc_html_e('Body Fat: %', 'keto-macro-calculator'); ?>
                        </label>
                        <div class="input-with-unit">
                            <input type="number" id="bodyFat" name="bodyFat" placeholder="20">
                        </div>
                        </div>

                        <div class="form-group goal">
                            <label for="goal" class="form-label">
                                <span class="dashicons dashicons-marker"></span>
                                <?php esc_html_e('Goal:', 'keto-macro-calculator'); ?>
                            </label>
                            <div class="select-wrapper">
                                <select id="goal" name="goal">
                                    <option value="lose"><?php esc_html_e('Lose Weight', 'keto-macro-calculator'); ?></option>
                                    <option value="gain"><?php esc_html_e('Gain Weight', 'keto-macro-calculator'); ?></option>
                                </select>
                                <span class="dashicons dashicons-arrow-down-alt2"></span>
                            </div>
                        </div>
                    </div>
                    
                </div>

                <div class="form-section">
                    <h3 class="form-section-title"><?php esc_html_e('Keto Settings', 'keto-macro-calculator'); ?></h3>
                    
                    <div class="form-group carbs">
                        <label for="net_carbs" class="form-label">
                            <span class="dashicons dashicons-carrot"></span>
                            <?php esc_html_e('Daily Net Carbs:', 'keto-macro-calculator'); ?><span class="unit">% of Calories</span>
                        </label>
                        <div class="input-with-unit">
                            <input type="number" id="net_carbs" name="net_carbs" step="0.1" min="5" max="10" placeholder="5 - 10">
                            
                        </div>
                        <p class="form-description">
                            <?php esc_html_e('Specify your target daily net carbs (typically 5 - 10 % of daily Calories or 20-30g/day to start ketosis)', 'keto-macro-calculator'); ?>
                        </p>
                    </div>
                </div>

                <button type="submit" class="keto-calc-btn">
                    <span class="dashicons dashicons-calculator"></span>
                    <?php esc_html_e('Calculate My Macros', 'keto-macro-calculator'); ?>
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
            
        <div id="ketoResults" style="visibility: hidden; opacity: 0; height: 0px">
            <div class="main-results">
                <div class="results-group">
                    <h2><?php esc_html_e('Your Results', 'keto-macro-calculator'); ?></h2>
                    <div class="result-group">
                        <h3>Your Current Base Metabolic Rate.</h3>
                        <p class="value"><?php esc_html_e('BMR:', 'keto-macro-calculator'); ?> <span id="bmrValue">--</span> kcal/day</p>
                        <p><i>Based on the more accurate <a href="https://en.wikipedia.org/wiki/Basal_metabolic_rate">Katch-McArdle formula</a> when body fat % is known</i></p>
                    </div>
                    <div class="result-group">
                        <h3>Daily Calories</h3>
                        <p class="value"><?php esc_html_e('Calories to Consume:', 'keto-macro-calculator'); ?> <span id="caloriesToConsume">--</span> kcal/day</p>
                        <p><i>Your daily TDEE "activity adjusted" Calorie Intake.</i></p>
                    </div>
                </div>
                <div class="chart-wrapper">
                    <div id="chart"></div>
                    <div class="chart-summary">
                        <p>
                            <?php 
                            echo esc_html__('A', 'keto-macro-calculator') . ' '; 
                            ?><span id="summaryAge">--</span><?php 
                            echo ' ' . esc_html__('year old', 'keto-macro-calculator') . ' '; 
                            ?><span id="summaryActivity">--</span><?php 
                            echo ' '; 
                            ?><span id="summaryGender">--</span><?php 
                            echo ', '; 
                            ?><span id="summaryHeight">--</span><?php 
                            echo ' ' . esc_html__('tall', 'keto-macro-calculator') . ', ' . esc_html__('weighing', 'keto-macro-calculator') . ' '; 
                            ?><span id="summaryWeight">--</span><?php 
                            echo ', ' . esc_html__('with a body fat % of', 'keto-macro-calculator') . ' '; 
                            ?><span id="summaryBodyFat">--</span><?php 
                            echo '.'; 
                            ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="form-description">
                <h3>Base Metabolic Rate (BMR)</h3>
                <p>Basal Metabolic Rate, (BMR) is the measure of how many calories you should consume daily at your current age, height, and weight – at rest.</p>
                <h3>Calories to consume</h3>
                <p>This is the “maintanence” level of calories, taking into account your current activity levels, “Total Daily Energy Expenditure” – (TDEE). This calorie value is YOUR recommended daily calorie intake to maintain your current weight.</p>
                <p>The main chart shows how this calorie recommendation should be divided into the main macronutrien groups, Fat, Protein and Carbs to initiate “ketosis” for a basic keto diet.</p>
                <h3>Weight Loss Goal</h3>
                <p>Set your target as ‘weight loss, or ‘weight gain’… then set the maximum carb content you want to include… the results then give you 3 levels of loss/gain with associated carbs, proteins & fats with calorie counts.</p>
            </div>
            
            <div class="variations-header">
                <h3>Weight Target Variations</h3>
                <p>For faster weight target achievement, consider these variations...</p>
            </div>
            
            <!-- Deficit Charts -->
            <div class="chart-slider-wrapper" id="calorieDeficitCharts" style="display: none;">
                <div class="chart-slider-container">
                    <div class="chart-slider">
                        <!-- Charts Injected Here by Javascript -->
                    </div>
                </div>
                <!-- navigation -->
                <div class="slider-nav-wrap" id="def-nav">
                    <button class="slider-nav prev deficit-nav"><strong>&larr;</strong></button>
                    <div class="slider-dots"></div>
                    <button class="slider-nav next deficit-nav"><strong>&rarr;</strong></button>
                </div>
            </div>
            
            <!-- Surplus Charts -->
            <div class="chart-slider-wrapper" id="calorieSurplusChart" style="display: none;">
                <div class="chart-slider-container">
                    <div class="chart-slider">
                    <!-- Charts Injected Here by Javascript -->
                    </div>
                </div>
                <!-- navigation -->
                 <div class="slider-nav-wrap" id="surp-nav">
                    <button class="slider-nav prev surplus-nav"><strong>&larr;</strong></button>
                    <div class="slider-dots"></div>
                    <button class="slider-nav next surplus-nav"><strong>&rarr;</strong></button>
                </div>
            </div>
        </div>    
    </div>
        
            

    <?php
    return ob_get_clean();  
}
add_shortcode('keto_macro_calculator', 'keto_macro_calculator');


