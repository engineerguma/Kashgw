<?php

class Fees {

    function __construct() {
        $this->log = new Log();
    }

    /*
     * Transaction Fees Computations
     */

    function ComputeTransactionFees($vndr, $revenue_model, $amount) {
        $this->log->LogToFile($vndr, "Fees::ComputeTransactionFees With Rev Model " . var_export($revenue_model, true) .
                " For Amount " . $amount, 2, 2);
        //Compute Aggregator Fees:
        $aggreg_rule_func = $revenue_model[0]['agg_rev_model'];
        $aggreg_amount = $this->{$aggreg_rule_func}('agg', $revenue_model, $amount);
        //Compute Operator Fees:
        $opco_rule_func = $revenue_model[0]['payserv_rev_mod'];
        $opco_amount = $this->{$opco_rule_func}('opco', $revenue_model, $amount);

        $data = array(
            'aggreg_fee' => $aggreg_amount,
            'pay_serv_fee' => $opco_amount
        );
        return $data;
    }

    function FixedTransFee($type, $br, $amt) {
        if ($type == 'agg') {
            $amount = $br[0]['agg_fee_val_one'];
        } else {
            $amount = $br[0]['payserv_fee_val_one'];
        }
        return $amount;
    }

    function FixedTansPerc($type, $br, $amt) {
        if ($type == 'agg') {
            $amount = ($amt / 1.18) * $br[0]['agg_fee_val_one'];
        } else {
            $amount = ($amt / 1.18) * $br[0]['payserv_fee_val_one'];
        }
        return $amount;
    }

    function RangedTransFee($type, $br, $amt) {
        if ($type == 'agg') {
            $range_id = $br[0]['agg_fee_val_one'];
        } else {
            $range_id = $br[0]['agg_fee_val_one'];
        }
        $range = $this->db->SelectData("SELECT * FROM mvd_rate_range_fees WHERE rate_range_id = :rid
                AND :amt BETWEEN min_amount AND max_amount", array('rid' => $range_id, 'amt' => $amt));
        $amount = $range[0]['fee'];
        return $amount;
    }

    function RangedTransPerc($type, $br, $amt) {
        if ($type == 'agg') {
            $range_id = $br[0]['agg_fee_val_one'];
        } else {
            $range_id = $br[0]['agg_fee_val_one'];
        }
        $range = $this->db->SelectData("SELECT * FROM mvd_rate_range_fees WHERE rate_range_id = :rid
                AND :amt BETWEEN min_amount AND max_amount", array('rid' => $range_id, 'amt' => $amt));
        $amount = $amt * $range[0]['fee'];
        return $amount;
    }

    function RangedFeePercMarkUp($type, $br, $amt) {
        if ($type == 'agg') {
            $range_id = $br[0]['agg_fee_val_one'];
        } else {
            $range_id = $br[0]['agg_fee_val_one'];
        }
        $range = $this->db->SelectData("SELECT * FROM mvd_rate_range_fees WHERE rate_range_id = :rid"
                . "AND ");
        $amount = $range[0]['fee'] * $range[0]['fee_mark_up'];
        return $amount;
    }

    function RangedPercWithFee($type, $br, $amt) {
        if ($type == 'agg') {
            $range_id = $br[0]['agg_fee_val_one'];
        } else {
            $range_id = $br[0]['agg_fee_val_one'];
        }
        $range = $this->db->SelectData("SELECT * FROM mvd_rate_range_fees WHERE rate_range_id = :rid"
                . "AND ");
        $amount = ($amt * $range[0]['fee']) + $range[0]['fee_mark_up'];
        return $amount;
    }

}
