<?php
/*******************************************************************************

    Copyright 2012 Whole Foods Co-op

    This file is part of IT CORE.

    IT CORE is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    IT CORE is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    in the file license.txt along with IT CORE; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*********************************************************************************/

/**
  @class RebateCheckTender
  Tender module for checks
*/
class RebateCheckTender extends TenderModule 
{

    /**
      Check for errors
      @return True or an error message string
    */
    public function errorCheck()
    {
        return true;
    }
    
    /**
      Set up state and redirect if needed
      @return True or a URL to redirect
    */
    public function preReqCheck()
    {
        if (CoreLocal::get("enableFranking") != 1) {
            return true;
        }

        // check endorsing
        if (CoreLocal::get("msgrepeat") == 0) {
            return $this->DefaultPrompt();
        }

        return true;
    }

    public function add()
    {
        /* Discount disabled Jan01
        $db = Database::tDataConnect();
        $query = 'SELECT SUM(total) as ttl FROM localtemptrans
                WHERE department IN (992, 991, 902)';
        $result = $db->query($query);
        $ignore = 0.00;
        if ($db->num_rows($result) > 0) {
            $row = $db->fetch_row($result);
            $ignore = (float)$row['ttl'];
        }
        if ( (CoreLocal::get('runningTotal') - $ignore) >= 50) {
            TransRecord::addhousecoupon('PATREBDISC', 703, -5.00);
        }
        */

        parent::add();
    }

    public function allowDefault()
    {
        return false;
    }

    public function defaultPrompt()
    {
        if (CoreLocal::get("enableFranking") != 1) {
            return parent::defaultPrompt();
        }

        if ($this->amount === False) {
            return parent::disabledPrompt();
        }

        $ref = trim(CoreLocal::get("CashierNo"))."-"
            .trim(CoreLocal::get("laneno"))."-"
            .trim(CoreLocal::get("transno"));

        $msg = "<br />"._("insert")." ".$this->name_string.
            ' for $'.sprintf('%.2f',$this->amount). '<br />';
        if (CoreLocal::get("LastEquityReference") == $ref) {
            $msg .= "<div style=\"background:#993300;color:#ffffff;
                margin:3px;padding: 3px;\">
                There was an equity sale on this transaction. Did it get
                endorsed yet?</div>";
        }

        CoreLocal::set("boxMsg",$msg);
        CoreLocal::set('strEntered', (100*$this->amount).$this->tender_code);
        CoreLocal::set('boxMsgButtons', array(
            'Endorse [enter]' => '$(\'#reginput\').val(\'\');submitWrapper();',
            'Cancel [clear]' => '$(\'#reginput\').val(\'CL\');submitWrapper();',
        ));

        return MiscLib::base_url().'gui-modules/boxMsg2.php?endorse=check&endorseAmt='.$this->amount;
    }

}

