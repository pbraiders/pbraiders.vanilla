<?php
/*************************************************************************
 *                                                                       *
 * Copyright (C) 2010   Olivier JULLIEN - PBRAIDERS.COM                  *
 * Tous droits réservés - All rights reserved                            *
 *                                                                       *
 *************************************************************************
 *                                                                       *
 * Except if expressly provided in a dedicated License Agreement,you     *
 * are not authorized to:                                                *
 *                                                                       *
 * 1. Use,copy,modify or transfer this software component,module or      *
 * product,including any accompanying electronic or paper documentation  *
 * (together,the "Software").                                            *
 *                                                                       *
 * 2. Remove any product identification,copyright,proprietary notices    *
 * or labels from the Software.                                          *
 *                                                                       *
 * 3. Modify,reverse engineer,decompile,disassemble or otherwise         *
 * attempt to reconstruct or discover the source code,or any parts of    *
 * it,from the binaries of the Software.                                 *
 *                                                                       *
 * 4. Create derivative works based on the Software (e.g. incorporating  *
 * the Software in another software or commercial product or service     *
 * without a proper license).                                            *
 *                                                                       *
 * By installing or using the "Software",you confirm your acceptance     *
 * of the hereabove terms and conditions.                                *
 *                                                                       *
 * file encoding: UTF-8                                                  *
 *                                                                       *
 *************************************************************************/
if( !defined('PBR_VERSION') || !defined('PBR_DB_LOADED') || !defined('PBR_DATE_LOADED') )
    die('-1');

/**
  * function: RentsGetCount
  * description: Get rents count for a specific date.
  * parameters: STRING|sLogin   - login identifier
  *             STRING|sSession - session identifier
  *             STRING|sInet    - concatenation of IP and USER_AGENT
  *              CDate|pDate    - requested date
  * return: BOOLEAN - FALSE if an exception occures
  *         or
  *         INTEGER - >=0 is OK. Number of row.
  *                   -1 when a private error occures.
  *                   -2 when an authentication error occures.
  *                   -3 when an access denied error occures.
  *                   -4 when a duplicate error occures.
  * author: Olivier JULLIEN - 2010-02-04
  * update: Olivier JULLIEN - 2010-05-24 - use ErrorDBLog instead of CErrorList::AddDB(...) and CDb::GetInstance()->LogError(...)
  */
function RentsGetCount( $sLogin, $sSession, $sInet, &$pDate)
{
	/** Initialize
     *************/
    $iReturn=-1;
    $sMessage='';
    $sErrorTitle=__FUNCTION__;
    $sErrorTitle.='('.$sLogin.','.$sSession.',[obfuscated],...)';

	/** Request
     **********/
    if( IsParameterScalarNotEmpty($sLogin)
        && IsParameterScalarNotEmpty($sSession)
        && IsParameterScalarNotEmpty($sInet)
        && !is_null($pDate)
        && CDb::GetInstance()->IsOpen()===TRUE )
    {
    	// Set error title
	    $sErrorTitle=__FUNCTION__;
	    $sErrorTitle.='('.$sLogin.','.$sSession.',[obfuscated],'.$pDate->GetRequestDay().','.$pDate->GetRequestMonth().','.$pDate->GetRequestYear().')';
		// Request
        if( CUser::GetInstance()->IsAuthenticated() )
        {
            // try
            try
            {
                // Prepare
				$sSQL='SELECT COUNT(r.`idreservation`) AS "rent_count" FROM `'.PBR_DB_DBN.'`.`reservation` AS r WHERE r.`year`=:iYear AND r.`month`=:iMonth AND r.`day`=:iDay';
                $pPDOStatement = CDb::GetInstance()->PDO()->prepare($sSQL);
                // Bind
                $pPDOStatement->bindParam(':iDay',$pDate->GetRequestDay(),PDO::PARAM_INT);
                $pPDOStatement->bindParam(':iMonth',$pDate->GetRequestMonth(),PDO::PARAM_INT);
                $pPDOStatement->bindParam(':iYear',$pDate->GetRequestYear(),PDO::PARAM_INT);
                // Execute
                $pPDOStatement->execute();
                // Fetch
                $tabResult = $pPDOStatement->fetchAll();
                // Analyse
                if( is_array($tabResult) && isset($tabResult[0]) && is_array($tabResult[0]) )
                {
                    if( array_key_exists('rent_count', $tabResult[0])===TRUE )
                    {
                        $iReturn=(integer)$tabResult[0]['rent_count'];
                    }//if( array_key_exists('rent_count', $tabResult[0])===TRUE )
                }//if( is_array($tabResult) && isset($tabResult[0]) && is_array($tabResult[0]) )
            }
            catch(PDOException $e)
            {
                $iReturn=FALSE;
                $sMessage=$e->getMessage();
            }//try

            // Free resource
            $pPDOStatement=NULL;
		}
        else
        {
        	$iReturn=-2;
        }//if( CUser::GetInstance()->IsAuthenticated() )
    }//if( IsParameterScalarNotEmpty(

    // Error
    ErrorDBLog( $sLogin, $sErrorTitle, $sMessage, $iReturn, TRUE);

    return $iReturn;
}

?>
