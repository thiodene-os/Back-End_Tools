<?php

// Main libraries for global page use

// Builds and returns the html menu based on user privileges
function buildUserMenu()
{
  global $user_mgr ;

  // ******************* First Create the Complete Full Menu with all Possible Options ************************
  
  $staff_menu = array() ;

  // Home menu
  $home_menu = array("text" => "Home"
                     ,"linked_to" => "/") ;
  $staff_menu[] = $home_menu ;
                        
  // Liqure Inventory                   
  $lq_mnu_items = array() ;
  $lq_mnu_items[] = array("text" => "Liquor Setup","linked_to" => "liquor_inv_base_info") ;
  $lq_mnu_items[] = array("text" => "Liquor Lists","linked_to" => "liquor_inv_liquor_lists") ;
  $lq_mnu_items[] = array("text" => "All Liquor Products","linked_to" => "liquor_products_list") ;
  $lq_mnu_items[] = array("text" => "Inventory Setup","linked_to" => "warehouse_setup") ;  
  $lq_mnu_items[] = array("text" => "Cycle Counts","linked_to" => "inv_count_list") ;
  $lq_mnu_items[] = array("text" => "Active Purchase Orders","linked_to" => "active_purchase_orders") ;
  $lq_mnu_items[] = array("text" => "Required Purchasing","linked_to" => "mb_po_short_list") ;
  $lq_mnu_items[] = array("text" => "Receiving","linked_to" => "po_receive_list") ;
  $lq_mnu_items[] = array("text" => "Liquor Checkout Console","linked_to" => "liquor_checkout") ;
  $lq_mnu_items[] = array("text" => "Liquor Inventory Report","linked_to" => "liquor_inventory_report") ;
  $lq_mnu_items[] = array("text" => "All Purchase Orders","linked_to" => "purchase_order_list") ;
  
  $staff_menu[] = array("text" => "Liquor"
                        ,"linked_to" => "liquor_inv_base_info"
                        ,"img" => "nav_liquor.png"
                        ,"mnu_items" => $lq_mnu_items) ;

  // Manage menu                      
  $manage_mnu_items = array() ;
  $manage_mnu_items[] = array("text" => "Email Console","linked_to" => "email_console") ;
  $manage_mnu_items[] = array("text" => "Marketing Documents","linked_to" => "marketing_documents") ;
  $manage_mnu_items[] = array("text" => "Itinerary Templates","linked_to" => "event_itinerary_tmpls") ;
  $manage_mnu_items[] = array("text" => "Email Queue","linked_to" => "email_queue_list") ;
  $manage_mnu_items[] = array("text" => "Staff","linked_to" => "mgr_staff_list") ;
  $manage_mnu_items[] = array("text" => "Staff Scheduling","linked_to" => "mgr_staff_schedule") ;
  $manage_mnu_items[] = array("text" => "Departments","linked_to" => "mgr_department_list") ;
  $manage_mnu_items[] = array("text" => "My Schedule","linked_to" => "user_staff_schedule") ;
  $manage_mnu_items[] = array("text" => "Restaurant Schedule","linked_to" => "manage_restaurant_schedule") ;
  $manage_mnu_items[] = array("text" => "Ad Manager","linked_to" => "mgr_ad_list") ;
  $manage_mnu_items[] = array("text" => "Coupons","linked_to" => "mgr_discount_coupon_list") ;
  
  $staff_menu[] = array("text" => "Manage"
                        ,"linked_to" => "email_console"
                        ,"img" => "nav_manage.png"
                        ,"mnu_items" => $manage_mnu_items) ;
                        
  // Customer menu                      
  $customers_mnu_items = array() ;
  $customers_mnu_items[] = array("text" => "New Customer","linked_to" => "customer_duplicate_serach") ;
  $customers_mnu_items[] = array("text" => "Customers","linked_to" => "customer_list") ;
  $customers_mnu_items[] = array("text" => "Events Calendar","linked_to" => "events_calendar") ;
  $customers_mnu_items[] = array("text" => "Availability Calendar","linked_to" => "room_availability_calendar") ;
  $customers_mnu_items[] = array("text" => "All Events","linked_to" => "event_list") ;
  $customers_mnu_items[] = array("text" => "All Floor Plans","linked_to" => "floor_plan_list") ;
  $customers_mnu_items[] = array("text" => "Special Events","linked_to" => "especial_events") ;
  $customers_mnu_items[] = array("text" => "Restaurant Reservations","linked_to" => "restaurant_reservation") ;
  $customers_mnu_items[] = array("text" => "Daily Restaurant Sales","linked_to" => "restaurant_daily_sales") ;
  $customers_mnu_items[] = array("text" => "All Gift Certificates","linked_to" => "gift_certificate_list") ;
  $customers_mnu_items[] = array("text" => "Email Templates","linked_to" => "user_email_template_list") ;
  $customers_mnu_items[] = array("text" => "Events Archive","linked_to" => "event_archive") ;
  
  $staff_menu[] = array("text" => "Customers"
                        ,"linked_to" => "customer_list"
                        ,"img" => "nav_customers.png"
                        ,"mnu_items" => $customers_mnu_items) ;

  // Purchasing menu                      
  $purchase_mnu_items = array() ;
  $purchase_mnu_items[] = array("text" => "Purchase Order Preparation","linked_to" => "po_list_preparation") ;
  $purchase_mnu_items[] = array("text" => "Active Purchase Orders","linked_to" => "po_list_on_hand") ;
  $purchase_mnu_items[] = array("text" => "Weekly Requirement","linked_to" => "weekly_items_requirement") ;
  $purchase_mnu_items[] = array("text" => "Monthly Requirement","linked_to" => "monthly_items_requirement") ;
  $purchase_mnu_items[] = array("text" => "Item Requirement for Catering","linked_to" => "monthly_catering_requirement") ;
  $purchase_mnu_items[] = array("text" => "Purchasing Short List","linked_to" => "po_short_list_view") ;
  
  $staff_menu[] = array("text" => "Purchasing"
                        ,"linked_to" => "po_list_preparation"
                        ,"img" => "nav_purchasing.png"
                        ,"mnu_items" => $purchase_mnu_items) ;

  // Reports menu                      
  $reports_mnu_items = array() ;
  $reports_mnu_items[] = array("text" => "Pending Tasks","linked_to" => "report_user_pending_tasks") ;
  $reports_mnu_items[] = array("text" => "Sales Details","linked_to" => "report_sales_detail") ;
  $reports_mnu_items[] = array("text" => "All Payments","linked_to" => "report_actual_payments") ;
  $reports_mnu_items[] = array("text" => "Booked Events","linked_to" => "report_event_list") ;
  $reports_mnu_items[] = array("text" => "Booking Details","linked_to" => "report_booking_details") ;
  $reports_mnu_items[] = array("text" => "Average Price Per Person","linked_to" => "report_avg_price_per_person") ;
  $reports_mnu_items[] = array("text" => "Booking Comparison","linked_to" => "report_booking_comparison") ;
  $reports_mnu_items[] = array("text" => "Sales Drilling","linked_to" => "report_sales_drilling") ;
  $reports_mnu_items[] = array("text" => "Line Item Sales","linked_to" => "report_line_item_sales") ;
  $reports_mnu_items[] = array("text" => "Yearly Sales by Event Type","linked_to" => "report_sales_by_event_type") ;
  $reports_mnu_items[] = array("text" => "Total Sales & Comparison","linked_to" => "report_total_sales_summary") ;
  $reports_mnu_items[] = array("text" => "Total Guests & Comparison","linked_to" => "report_total_guest_summary") ;
  $reports_mnu_items[] = array("text" => "Sales by Salesperson","linked_to" => "report_sales_by_sales_person") ;
  $reports_mnu_items[] = array("text" => "Monthly Cash Flow","linked_to" => "report_cash_flow_monthly") ;
  $reports_mnu_items[] = array("text" => "Cash Flow/Payments","linked_to" => "report_cash_flow") ;
  $reports_mnu_items[] = array("text" => "Revenue Details","linked_to" => "report_revenue_details") ;
  $reports_mnu_items[] = array("text" => "Commission Report","linked_to" => "mgr_commission_list") ;
  $reports_mnu_items[] = array("text" => "Accounting Synch","linked_to" => "acct_synch_list") ;
  
  $staff_menu[] = array("text" => "Reports"
                        ,"linked_to" => "report_event_list"
                        ,"img" => "nav_reports.png"
                        ,"mnu_items" => $reports_mnu_items) ;
                        
  // Base info menu                      
  $base_info_mnu_items = array() ;
  $base_info_mnu_items[] = array("text" => "Suppliers","linked_to" => "supplier_list") ;
  $base_info_mnu_items[] = array("text" => "Products","linked_to" => "product_list") ;
  $base_info_mnu_items[] = array("text" => "Product Archive","linked_to" => "product_archive_list") ;
  $base_info_mnu_items[] = array("text" => "Adv. Sources","linked_to" => "customer_ad_source_list") ;
  $base_info_mnu_items[] = array("text" => "Categories","linked_to" => "product_cat_list") ;
  $base_info_mnu_items[] = array("text" => "Facilities","linked_to" => "facility_list") ;
  $base_info_mnu_items[] = array("text" => "Floor Plans","linked_to" => "floor_plan_settings") ;
  $base_info_mnu_items[] = array("text" => "Event Type","linked_to" => "event_type_list") ;
  $base_info_mnu_items[] = array("text" => "Global Settings","linked_to" => "sys_setting_list") ;
  $base_info_mnu_items[] = array("text" => "City Lookup","linked_to" => "city_lookup_list") ;
  
  $staff_menu[] = array("text" => "Base Info"
                        ,"linked_to" => "product_list"
                        ,"img" => "nav_base_info.png"
                        ,"mnu_items" => $base_info_mnu_items) ;

  // ******************** Build Final Menu with Only Options Accessible by User **********************
  
  
  
  $final_menu = "" ;
  $error_msg = "" ; // Dummy to return error message back and not used here
  
  // Tablet friendly drop down menu;
  foreach($staff_menu as $cur_menu) 
  {
    $page_id = $cur_menu['linked_to'] ;
    if ($page_id == "#landing_page#")
      $page_id = $user_mgr -> getUserLandingPage() ;
    $top_menu = "<li>" . buildHRef($cur_menu['text'],$page_id) ;

    if (isset($cur_menu['img']))
      $img = $cur_menu['img'] ;
    else
      $img = 'nav_home.png' ;
    
    $top_menu = str_replace('title="">',"title='' aria-haspopup=\"true\">
                  <img src=\"/" . images_folder . "/nav/" . $img . "\" 
                        alt=\"" . $home_menu['text'] . "\" />",$top_menu);
    
    $addh1 = FALSE;
    if (isset($cur_menu['mnu_items']))
    {
      // Make the top menu that has drop down options active for popup
      $addh1 = TRUE;
    
      $submenu = "" ;
      foreach($cur_menu['mnu_items'] as $cur_submenu) 
      {
        $page_id = $cur_submenu['linked_to'] ;
        $page_root = getSysObjectRoot($page_id,SYS_OBJECT_TYPE_PAGE); 
        if ($user_mgr -> isObjectAccessible($page_root,$error_msg))
          $submenu .= "<li>" . buildHRef($cur_submenu['text'],$page_id) . "</li>" ;
      }  
      if ($addh1)
      {
        $top_menu .= "<ul><h1>" . $home_menu['text'] . "</h1>" . $submenu . "</ul>" ;
      }
      else
      {
        $top_menu .= "<ul>" . $submenu . "</ul>" ;
      }        
    }      
    $top_menu .= "</li>" ;
  
    // If submenu exists but user has access to none of them, then we should not show this whole menu
    if (! (isset($cur_menu['mnu_items']) && empty($submenu)))
      $final_menu .= $top_menu ;
  }

  // Add the wrap around markup
  $final_menu = '<nav id="sidebar" role="navigation">'
                  . '<a href="#sidebar" title="Show navigation">Show navigation</a>'
                  . '<a href="#" title="Hide navigation">Hide navigation</a>'
                    . '<ul class="clearfix">'
                    . '<a href="http://sims2.scentroid.com" target="_blank"><img  src="/' . IMAGES_FOLDER . '/scentroid.png" /></a>'                      
                    . $final_menu
                . '</ul>
                 </nav>
                <script src="https://osvaldas.info/examples/drop-down-navigation-touch-friendly-and-responsive/doubletaptogo.js"></script>
                <script>
                  $( function()
                  {
                    $("#sidebar li:has(ul)").doubleTapToGo() ;
                  });
                </script>' ;

  return $final_menu ;
} // buildUserMenu

?>
