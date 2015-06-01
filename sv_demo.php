<?php
/* ====================
[BEGIN_HONG]
File=plugins/standard/sv_demo/sv_demo.php
Version=1.1
Updated=4/10/2015
Type=Plugin.standard
Author=Hong Shen
Description=a demo of code documentation and refactoring
            contact: Evaldas Alexander alex@simplexity.ventures
[END_HONG]
==================== */
//if ( !defined('HONG_CODE') OR !defined('HONG_PLUG') ) { die("Wrong URL."); }
//hong_accessonly($usr['level']>=80);

$plugin_title='<h1>Board Vote Report Demo</h1>';

/////////////////////////////

/**
 * Generate HTML code to display a board vote item status
 *                         (Approved, Rejected or Pending)
 *
 * @param INT $id, id of the items that is being process.
 * @param INT $approved, if it is approved. value 1 for yes and 0 for no
 * @param INT Unix timestamp $a_date, the time of approval.
 * @param INT $rejected, if it is rejected.
 * @param INT $r_date, the time of rejection.
 * @param INT $required_count, required counts to approve the item, globally defined
 * @param DATABASE class instance $db
 *
 * @return string in html reflecting the status of the item
 */
function item_status($id, $approved, $a_date, $rejected,$r_date, $required_count, &$db)
{
	///Local vars to display status with graphics.
	$approve = "<img src='http://icf-xchange.org/graphics/img/approval.gif' border='0' alt='APPROVE' />";
	$reject = "<img src='http://icf-xchange.org/graphics/img/reject.gif' border='0' alt='REJECT' />";
	$pending = "<img src='http://icf-xchange.org/graphics/img/pending.gif' border='0' alt='Pending' />";
	if($approved=='1')
	{
	    $return = $approve." on ".date('F d, Y',$a_date);
	}
	elseif($rejected=='1')
	{
	    $return = $reject." on ".date('F d, Y',$r_date);
	}
	else
	{
		$votedNum = $db->get_var("select count(pv_id) from hong_poll_voters where pi_id='$id' and pv_content='APPROVE'");
		$neededVotes=$required_count - $votedNum;
		$return .=$pending." <u>$neededVotes Votes needed</u>";
	}
	return $return;
}

/**
 * Convert to standard html for display
 *
 * @param string $text
 * @return string in standard html
 */
function clean_text($text)
{
	$text = str_replace('\\\'', '&#039;', $text);
	$text = str_replace('\\"', '&quot;', $text);
	$text = str_replace(
	array('{',  '$', '\'', '"', '\\'),
	array('&#123;', '&#036;', '&#039;', '&quot;', '&#92;'), $text);
	return($text);
}

/**
 * Compose a report table for a board vote item.
 *
 * @param INT $item_id, id of the item
 * @param DATABASE instance, $db
 * @param INT $required_count, the approval count to approve the item.
 *        (globally define)
 * @param BOOLEAN $showvoters, deternine wheather to display voters name.
 *
 * @return STRING, report table for the itme in standard html
 *
 * @dependency: system/functions.php
 */

function poll_generate_table($item_id, &$db,$required_count, $showvoters=true)
{
	//to retrive the item
	$res=$db->get_row("select * from hong_poll_items where pi_id='$item_id'");
	//to get votes
	$votes=$db->get_results("select * from hong_poll_voters where pi_id='$item_id'");
    //to compose the report table
	$report_table.="<center><table class='block' width='95%' cellspacing='3'><tr><td width='25%' align='right'><strong>Item title:</strong></td><td align='left'>";
    if(!empty($res->pi_num))
    {
		$report_table.='#'.$res->pi_num.'&nbsp;';
	}
    if(!empty($res->pi_nature))
    {
	    $report_table.= $res->pi_nature.'&nbsp;';
    }
	$report_table.=clean_text(stripslashes($res->pi_title))."</td></tr>";
	$report_table.= (empty($res->pi_location))? '':"<tr><td align='right'><strong>Location:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_location))."</td></tr>";

    $report_table.= (empty($res->pi_beneficiary))? '':"<tr><td align='right'><strong>Grantee:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_beneficiary))."</td></tr>";

    if(!empty($res->pi_new_fund_name))
    {
        $report_table.= "<tr><td align='right'><strong>New Fund Name:</strong></td><td align='left'>";

        if(empty($res->pi_fund_url))
        {
            $report_table.= clean_text(stripslashes($res->pi_new_fund_name));
        }
        else
        {
            $report_table.= "<a href='".$res->pi_fund_url."'>".clean_text(stripslashes($res->pi_new_fund_name))."</a>";
        }
        $report_table.= "</td></tr>";
    }
    $report_table.= (empty($res->pi_new_fund_donor))? '':"<tr><td align='right'><strong>Donors</strong></td><td align='left'>".clean_text(stripslashes($res->pi_new_fund_donor))."</td></tr>";
    $report_table.= (empty($res->pi_new_fund_donor_advisor))? '':"<tr><td align='right'><strong>Donor Advisors</strong></td><td align='left'>".clean_text(stripslashes($res->pi_new_fund_donor_advisor))."</td></tr>";

    $report_table.= (empty($res->pi_initial_investment))? '':"<tr><td align='right'><strong>Initial Investment:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_initial_investment))."</td></tr>";

    $report_table.= (empty($res->pi_fund_type))? '':"<tr><td align='right'><strong>Fund Type:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_fund_type))."</td></tr>";

    $report_table.= (empty($res->pi_amount))? '':"<tr><td align='right'><strong>Amount:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_amount))."</td></tr>";

    $report_table.= (empty($res->pi_fund))?'':"<tr><td align='right'><strong>Fund Name:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_fund))."</td></tr>";

    $report_table.= (empty($res->pi_fund_num))?'':"<tr><td align='right'><strong>Fund#:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_fund_num))."</td></tr>";
    $report_table.= (empty($res->pi_grant_fund_type))?'':"<tr><td align='right'><strong>Fund Type:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_grant_fund_type))."</td></tr>";

    $report_table.= (empty($res->pi_purpose))?'':"<tr valign='top'><td align='right'><strong>Purpose:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_purpose))."</td></tr>";

    $report_table.= (empty($res->pi_rationale))?'':"<tr valign='top'><td align='right'><strong>Rationale:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_rationale))."</td></tr>";

    if(strlen($res->pi_grantee_website)>60)
	{
		$web=substr($res->pi_grantee_website, 0, 15).'.....'.substr($res->pi_grantee_website, -10);
	}
	else
	{
		$web=$res->pi_grantee_website;
	}

    $report_table.= (empty($res->pi_grantee_website))? '':"<tr><td align='right'><strong>Grantee Website:</strong></td><td align='left'><a href=".$res->pi_grantee_website." target=_blank>".$web."</a></td></tr>";

    $report_table.=(empty($res->pi_risk))?'':"<tr><td align='right'><strong>Risk Ranking:</strong></td><td align='left'>".$res->pi_risk."</td></tr>";

    $report_table.=(empty($res->pi_risk_expl))?'':"<tr valign='top'><td align='right'><strong>Risk Ranking Explain:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_risk_expl))."</td></tr>";

    $report_table.=(empty($res->pi_recused))?'':"<tr valign='top'><td align='right'><strong>Recused members:</strong></td><td align='left'>".clean_text(stripslashes($res->pi_recused))."</td></tr>";

	$report_table.=(empty($res->pi_icfstaff))?'':"<tr valign='top'><td align='right'><strong>ICF Staff:</strong></td><td align='left'>".$res->pi_icfstaff."</td></tr>";

    $report_table.="<tr><td align='right'><strong>Voting Status:</strong></td><td align='left'>".item_status($res->pi_id, $res->pi_passed, $res->pi_passed_date, $res->pi_rejected, $res->pi_rejected_date, $required_count, $db)."</td></tr>";

	//compose voter list table
	if($showvoters)
	{
    $report_table.="<tr><td align='left'>&nbsp;</td><td align='center'>
					   <table width='90%' class='blueForm'>";
                        if(empty($votes))
                        {
                            $report_table.="<tr><td align='left'>No one has voted.</td></tr>";
                        }
                        else
                        {
                            foreach($votes as $vote)
                            {
                                $report_table.="<tr><td align='left'>".$vote->pv_fname."&nbsp;".$vote->pv_lname." voted ".$vote->pv_content." on ".date("l, F dS, Y", $vote->pv_creationdate)."</td></tr>";
                            }
                        }
				      $report_table.="</table></td></tr>";
	}
    $report_table.="</table></center>";
    return($report_table);
}

/**
 * Add title to item table
 *
 * @param STRING $rawtable
 * @param STRING $title
 * @return STRING table with the title
 */

function add_table_title($rawtable, $title,$cssclass)
{
	if(!empty($rawtable))
	{
		$titled_table = "<center><table width='100%' border='0' cellspacing='3' cellpadding='0' class='".$cssclass."'>
          <tr><td class='expanded' align='center'><strong><u>".$title."</u></strong></td></tr>
          <tr><td align='center'>".$rawtable."</td></tr></table></center>";
		return $titled_table;
	}
    else {
		return '';
    }
}

//hard coded $pa_d $group_id for demo purpose
$pa_d = 'view_report'; $group_id=3650;

//copose Board Vote report table
if($pa_d=='view_report')
{
	//retrieve the group title
	$group_title=$db->get_var("select group_title from hong_poll_groups where group_id='".$group_id."'");

	//retrive items
	$items = $db->get_results("select * from hong_poll_items where group_id='".$group_id."' order by pi_creationdate ASC");

	if(!empty($items))
	{
		foreach($items as $item)
		{
			switch($item->pi_nature)
			{
				case 'Fund':
					$fund_item_tables .= poll_generate_table($item->pi_id,$db,$board_vote_pass_count);
				    break;
				case 'Charitable Expense':
					$charitable_expense_item_tables .= poll_generate_table($item->pi_id,$db,$board_vote_pass_count);
				    break;
				case 'Grant':
					$grant_item_tables .= poll_generate_table($item->pi_id,$db,$board_vote_pass_count);
				    break;
				case 'Other':
					$other_item_tables .= poll_generate_table($item->pi_id,$db,$board_vote_pass_count);
					break;
				default:
					break;
			}
		}
	}

	$item_tables = add_table_title($fund_item_tables, 'New Fund Votes', 'fundForm')
		          .add_table_title($charitable_expense_item_tables, 'Charitable Expense','expenseForm')
		          .add_table_title($grant_item_tables, 'Grant Votes', 'grantForm')
		          .add_table_title($other_item_tables, 'Other Votes','otherForm');
	$print_output = $item_tables;

	//prepare print-friendly version button
	$report_form =
			"<form target=_blank method='post' action='print_voting_report.php?pr=".$rand."'>
			<input type='hidden' name='print' value='".htmlspecialchars($print_output,ENT_QUOTES)."'>
			<input type='hidden' name='group_title' value='".$group_title."' />
			<input type='submit' name='' value='Print-Friendly Version'>
			</form>";
	$report_table =
          "<center><table width='100%'>
		      <tr><td align='right'><a href='plug.php?p=polls_admin'>Back to Grant Cycle Management</a></td></tr>
              <tr><td class='expanded'><strong>Report for ".$group_title."</strong></td></tr>";
	$report_table .="<tr><td align='center'>".$report_form."</td></tr>";
	$report_table .="<tr><td>".$item_tables."</td></tr>";
	$report_table.="<tr><td align=center>";
	$report_table.=$report_form."</td></tr></table></center>";
}

/////////////////////////////////

$plugin_body=$report_table;

?>