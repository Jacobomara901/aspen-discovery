package org.aspen_discovery.grouping;

import com.turning_leaf_technologies.indexing.RecordIdentifier;
import com.turning_leaf_technologies.logging.BaseIndexingLogEntry;
import org.apache.logging.log4j.Logger;
import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.Locale;

public class BorrowBoxRecordGrouper extends RecordGroupingProcessor {
	private PreparedStatement getBorrowBoxProductInfoStmt;
	private PreparedStatement getProductMetadataStmt;

	public BorrowBoxRecordGrouper(Connection dbConnection, String serverName, BaseIndexingLogEntry logEntry, Logger logger) {
		super(dbConnection, serverName, logEntry, logger);

		try {
			getBorrowBoxProductInfoStmt = dbConnection.prepareStatement("SELECT id, mediaType, title, subtitle, series, primaryCreatorName from borrowbox_api_products WHERE borrowboxId = ?");
			getProductMetadataStmt = dbConnection.prepareStatement("SELECT UNCOMPRESS(rawData) as rawData from borrowbox_api_product_metadata where productId = ?", ResultSet.TYPE_FORWARD_ONLY, ResultSet.CONCUR_READ_ONLY);
		} catch (SQLException e) {
			logEntry.incErrors("Unable to setup BorrowBox statements", e);
		}
	}

	public String processBorrowBoxRecord(String borrowboxId) {
		try {
			getBorrowBoxProductInfoStmt.setString(1, borrowboxId);
			ResultSet borrowBoxRecordRS = getBorrowBoxProductInfoStmt.executeQuery();
			if (borrowBoxRecordRS.next()) {
				long id = borrowBoxRecordRS.getLong("id");
				String mediaType = borrowBoxRecordRS.getString("mediaType");
				String title = borrowBoxRecordRS.getString("title");
				String subtitle = borrowBoxRecordRS.getString("subtitle");
				String series = borrowBoxRecordRS.getString("series");
				String author = borrowBoxRecordRS.getString("primaryCreatorName");
				String primaryLanguage = "eng";

				getProductMetadataStmt.setLong(1, id);
				ResultSet metadataRS = getProductMetadataStmt.executeQuery();
				if (metadataRS.next()) {
					String rawDataStr = metadataRS.getString("rawData");
					if (rawDataStr != null && !rawDataStr.isEmpty()) {
						try {
							JSONObject productMetadata = new JSONObject(rawDataStr);
							if (productMetadata.has("languageDetails")) {
								JSONArray languagesFromMetadata = productMetadata.getJSONArray("languageDetails");
								if (languagesFromMetadata.length() > 1) {
									primaryLanguage = "mul";
								} else if (languagesFromMetadata.length() == 1) {
									JSONObject curLanguageObj = languagesFromMetadata.getJSONObject(0);
									String languageCode = curLanguageObj.getString("code");
									String threeLetterCode = translateValue("two_to_three_character_language_codes", languageCode.toLowerCase());
									if (threeLetterCode != null) {
										primaryLanguage = threeLetterCode;
									}
								}
							}
						} catch (JSONException e) {
							logEntry.incErrors("Error loading raw data for BorrowBox MetaData for record " + borrowboxId, e);
						}
					}
				}
				metadataRS.close();
				borrowBoxRecordRS.close();
				return processBorrowBoxRecord(borrowboxId, title, subtitle, series, author, mediaType, primaryLanguage);
			}
			borrowBoxRecordRS.close();
		} catch (SQLException e) {
			logEntry.incErrors("Error getting information about BorrowBox record for grouping", e);
		}
		return null;
	}

	private String processBorrowBoxRecord(String borrowboxId, String title, String subtitle, String series, String author, String mediaType, String primaryLanguage) {
		RecordIdentifier primaryIdentifier = new RecordIdentifier("borrowbox", borrowboxId);

		if (subtitle != null && !subtitle.isEmpty() && series != null && !series.isEmpty() && subtitle.toLowerCase(Locale.ROOT).contains(series.toLowerCase())) {
			subtitle = "";
		}

		return processRecord(primaryIdentifier, title, subtitle, author, mediaType, primaryLanguage, true);
	}
}
