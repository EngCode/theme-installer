/**
 * PDS Theme Installer JavaScript
 *
*/

//=====> Initialize When DOM is Ready <=====//
document.addEventListener('DOMContentLoaded', () => {
    //=====> Variables <=====//
    let ajaxUrl = '/wp-admin/admin-ajax.php';

    //====> Get AJAX URL <====//
    const ajaxInput = document.querySelector('input[name="pds_ajax_url"]');
    if (ajaxInput) ajaxUrl = ajaxInput.value;
    
    //====> Setup Initial Animations <====//
    const stepContent = document.querySelector('.step-content');
    if (stepContent) stepContent.classList.add('pds-fade-in');

    //====> Animate Plugin Items <====//
    const pluginItems = document.querySelectorAll('.plugin-item');
    pluginItems.forEach(function(item, index) {
        item.classList.add('pds-fade-in-up');
        item.style.setProperty('--animation-delay', (index * 100) + 'ms');
    });

    //=====> Show Message Helper <=====//
    const showMessage = (message, type) => {
        //====> Get Type <====//
        type = type || 'info';

        //====> Remove existing message <====//
        const existingMessage = document.querySelector('.installer-message');
        if (existingMessage) existingMessage.remove();

        //====> Create and insert new message <====//
        const messageEl = document.createElement('div');
        messageEl.className = 'installer-message message ' + type + ' pds-fade-in';
        messageEl.textContent = message;
        
        //====> Insert message at the top of step content <====//
        const stepContent = document.querySelector('.step-content');
        if (stepContent) stepContent.insertBefore(messageEl, stepContent.firstChild);

        //====> Auto-remove success messages <====//
        if (type === 'success') {
            //====> Set timeout to fade out and remove message <===//
            setTimeout(() => {
                //====> Add fade-out class <===//
                messageEl.classList.add('pds-fade-out');
                //====> Set timeout to remove message after fade-out <===//
                setTimeout(() => {
                    if (messageEl.parentNode) messageEl.remove();
                }, 500);
            }, 3000);
        }
    }
    
    //=====> Get Step URL Helper <=====//
    const getStepUrl = (step) => {
        // Check if we're in admin context
        if (window.location.href.includes('/wp-admin/')) {
            return window.location.href.split('?')[0] + '?pds_installer=true&step=' + step;
        } else {
            // Fallback to admin URL
            return '/wp-admin/admin.php?pds_installer=true&step=' + step;
        }
    }

    //=====> Dynamic Form Submission Handler <=====//
    const handleFormSubmission = (form, config) => {
        //====> Get Form Elements <====//
        const button = form.querySelector('button[type="submit"]');
        const progressContainer = form.querySelector('.install-progress, .import-progress');

        //====> Run validation if provided <====//
        if (config.validation && !config.validation()) return;

        //====> Show progress and disable button <====//
        if (progressContainer) progressContainer.classList.add('pds-show');
        if (button) {
            button.disabled = true;
            button.textContent = config.loadingText;
        }

        //====> Prepare form data <====//
        const formData = new FormData();
        formData.append('action', config.action);
        
        //===> Get nonce input and append to form data <====//
        const nonceInput = document.querySelector('input[name="pds_nonce"]');
        if (nonceInput) formData.append('nonce', nonceInput.value);

        //===> Add custom data if provided <====//
        if (config.prepareData) config.prepareData(formData);

        //===> Send AJAX request <====//
        fetch(ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        //====> Convert response to JSON <====//
        .then(function(response) {
            //====> Check if response is OK <====//
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            //====> Parse JSON response <====//
            return response.json();
        })
        //====> Process result <====//
        .then(function(result) {            
            //===> Check if result is successful <===//
            if (result.success) {
                //====> Show success message <====//
                showMessage(config.successMessage, 'success');
                //====> Set timeout to redirect to next step <====//
                if (config.nextStep) setTimeout(() => window.location.href = getStepUrl(config.nextStep), 1500);
            } 
            //====> Handle error case <====//
            else {
                const errorMessage = result.data && result.data.message ? result.data.message : config.errorMessage;
                throw new Error(errorMessage);
            }
        })
        //====> Handle errors <====//
        .catch(function(error) {
            //====> Show error message <====//
            console.error('Form submission error:', error);
            showMessage(error.message || config.errorMessage, 'error');

            //====> Reset button and hide progress <====//
            if (button) {
                button.disabled = false;
                button.textContent = 'إعادة المحاولة';
            }

            //====> Hide progress indicator <====//
            if (progressContainer) progressContainer.classList.remove('pds-show');
        });
    }

    //====> Handle all installer forms dynamically <====//
    document.addEventListener('submit', function(e) {
        //====> Define Form Element <====//
        const form = e.target;
        
        //====> Check if it's an installer form <====//
        if (form.id === 'plugins-form') {
            //====> Prevent Default Form Submission <====//
            e.preventDefault();
            //====> Handle Plugin Installation Form Submission <====//
            handleFormSubmission(form, {
                action: 'pds_install_plugins',
                loadingText: 'Installing...',
                successMessage: 'Plugins installed successfully!',
                errorMessage: 'An error occurred while installing plugins',
                nextStep: 'content',
                //====> Validation Function <====//
                validation: () => {
                    //====> Check if at least one plugin is selected <====//
                    const selected = form.querySelectorAll('input[name="plugins[]"]:checked');
                    //====> Show error if no plugins selected <====//
                    if (selected.length === 0) {
                        showMessage('Please select at least one plugin', 'error');
                        return false;
                    }
                    //====> All plugins are valid <====//
                    return true;
                },
                //====> Prepare Data Function <====//
                prepareData: (formData) => {
                    //====> Collect selected plugins <====//
                    const selectedPlugins = Array.from(form.querySelectorAll('input[name="plugins[]"]:checked')).map(function(cb) {
                        return cb.value;
                    });
                    //====> Append selected plugins to form data as array <====//
                    selectedPlugins.forEach(plugin => formData.append('plugins[]', plugin));
                }
            });
        }
        //====> Handle Content Import Form Submission <====//
        else if (form.id === 'content-form') {
            e.preventDefault();
            handleFormSubmission(form, {
                action: 'pds_import_content',
                loadingText: 'Importing...',
                successMessage: 'Content imported successfully!',
                errorMessage: 'An error occurred while importing content',
                nextStep: 'complete'
            });
        }
    });

    //=====> Plugin Selection Handler <=====//
    document.addEventListener('change', function(e) {
        //===> Check if the changed element is a plugin checkbox <=====//
        if (e.target.matches('input[name="plugins[]"]')) {
            //===> Update plugin item visual state <===//
            const pluginItem = e.target.closest('.plugin-item');
            //===> Toggle selected class based on checkbox state <===//
            if (pluginItem) pluginItem.classList.toggle('selected', e.target.checked);
            
            //=====> Update Install Button State <=====//
            const selectedCount = document.querySelectorAll('input[name="plugins[]"]:checked').length;
            const installButton = document.getElementById('install-plugins-btn');
            
            //=====> If install button doesn't exist, exit <=====//
            if (!installButton) return;
            
            //=====> Disable button if no plugins selected <=====//
            installButton.disabled = selectedCount === 0;
            //=====> Update button text based on selected count <=====//
            installButton.textContent = selectedCount === 0 ? 'Install Selected Plugins' : selectedCount === 1 ? 'Install Selected Plugin' : 'Install ' + selectedCount + ' Selected Plugins';
        }
    });
});

