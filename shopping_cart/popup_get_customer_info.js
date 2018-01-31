    <script type="text/javascript">
      $(function()
      {
        // If cater_cust_id is not passed through command line, it means
        // user has not yet entered his/her info
        if ("%show_popup%" == 'yes')
          setTimeout('loadCustomerAccountHandler() ;',500); // Give initAjaxEngine() enough time to initialize
      }) ;
      
      function loadCustomerAccountHandler()
      {
        loadAjaxObject('obj=page&page_id=ordering_get_customer_profile') ;
      } // loadCustomerAccountHandler
    </script>
