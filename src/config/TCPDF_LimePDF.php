<?php
namespace LimePDF;

require_once __DIR__ . '/../../tcpdf.php'; // path to original TCPDF

class TCPDF extends \TCPDF 
{
  
    public function getCleaned_ids() {
        return $this->cleaned_ids;
    }

    public function setCleaned_ids($value) {
        $this->cleaned_ids = $value;
        return $this;
    }
    public function getUtils() {
        return $this->utils;
    }

    public function setUtils($value) {
        $this->utils = $value;
        return $this;
    }
    public function getUtilsPut() {
        return $this->utilsPut;
    }

    public function setUtilsPut($value) {
        $this->utilsPut = $value;
        return $this;
    }
    public function getUtilsMisc() {
        return $this->utilsMisc;
    }

    public function setUtilsMisc($value) {
        $this->utilsMisc = $value;
        return $this;
    }
    public function getPage() {
        return $this->page;
    }

    public function setPage($value) {
        $this->page = $value;
        return $this;
    }
    public function getN() {
        return $this->n;
    }

    public function setN($value) {
        $this->n = $value;
        return $this;
    }
    public function getOffsets() {
        return $this->offsets;
    }

    public function setOffsets($value) {
        $this->offsets = $value;
        return $this;
    }
    public function getPageobjects() {
        return $this->pageobjects;
    }

    public function setPageobjects($value) {
        $this->pageobjects = $value;
        return $this;
    }
    public function getBuffer() {
        return $this->buffer;
    }

    public function setBuffer($value) {
        $this->buffer = $value;
        return $this;
    }
    public function getPages() {
        return $this->pages;
    }

    public function setPages($value) {
        $this->pages = $value;
        return $this;
    }
    public function getState() {
        return $this->state;
    }

    public function setState($value) {
        $this->state = $value;
        return $this;
    }
    public function getCompress() {
        return $this->compress;
    }

    public function setCompress($value) {
        $this->compress = $value;
        return $this;
    }
    public function getCurOrientation() {
        return $this->CurOrientation;
    }

    public function setCurOrientation($value) {
        $this->CurOrientation = $value;
        return $this;
    }
    public function getPagedim() {
        return $this->pagedim;
    }

    public function setPagedim($value) {
        $this->pagedim = $value;
        return $this;
    }
    public function getK() {
        return $this->k;
    }

    public function setK($value) {
        $this->k = $value;
        return $this;
    }
    public function getFwPt() {
        return $this->fwPt;
    }

    public function setFwPt($value) {
        $this->fwPt = $value;
        return $this;
    }
    public function getFhPt() {
        return $this->fhPt;
    }

    public function setFhPt($value) {
        $this->fhPt = $value;
        return $this;
    }
    public function getWPt() {
        return $this->wPt;
    }

    public function setWPt($value) {
        $this->wPt = $value;
        return $this;
    }
    public function getHPt() {
        return $this->hPt;
    }

    public function setHPt($value) {
        $this->hPt = $value;
        return $this;
    }
    public function getW() {
        return $this->w;
    }

    public function setW($value) {
        $this->w = $value;
        return $this;
    }
    public function getH() {
        return $this->h;
    }

    public function setH($value) {
        $this->h = $value;
        return $this;
    }
    public function getLMargin() {
        return $this->lMargin;
    }

    public function setLMargin($value) {
        $this->lMargin = $value;
        return $this;
    }
    public function getRMargin() {
        return $this->rMargin;
    }

    public function setRMargin($value) {
        $this->rMargin = $value;
        return $this;
    }
    public function getClMargin() {
        return $this->clMargin;
    }

    public function setClMargin($value) {
        $this->clMargin = $value;
        return $this;
    }
    public function getCrMargin() {
        return $this->crMargin;
    }

    public function setCrMargin($value) {
        $this->crMargin = $value;
        return $this;
    }
    public function getTMargin() {
        return $this->tMargin;
    }

    public function setTMargin($value) {
        $this->tMargin = $value;
        return $this;
    }
    public function getBMargin() {
        return $this->bMargin;
    }

    public function setBMargin($value) {
        $this->bMargin = $value;
        return $this;
    }
    public function getCell_padding() {
        return $this->cell_padding;
    }

    public function setCell_padding($value) {
        $this->cell_padding = $value;
        return $this;
    }
    public function getCell_margin() {
        return $this->cell_margin;
    }

    public function setCell_margin($value) {
        $this->cell_margin = $value;
        return $this;
    }
    public function getX() {
        return $this->x;
    }

    public function setX($value) {
        $this->x = $value;
        return $this;
    }
    public function getY() {
        return $this->y;
    }

    public function setY($value) {
        $this->y = $value;
        return $this;
    }
    public function getLasth() {
        return $this->lasth;
    }

    public function setLasth($value) {
        $this->lasth = $value;
        return $this;
    }
    public function getLineWidth() {
        return $this->LineWidth;
    }

    public function setLineWidth($value) {
        $this->LineWidth = $value;
        return $this;
    }
    public function getCoreFonts() {
        return $this->CoreFonts;
    }

    public function setCoreFonts($value) {
        $this->CoreFonts = $value;
        return $this;
    }
    public function getFonts() {
        return $this->fonts;
    }

    public function setFonts($value) {
        $this->fonts = $value;
        return $this;
    }
    public function getFontFiles() {
        return $this->FontFiles;
    }

    public function setFontFiles($value) {
        $this->FontFiles = $value;
        return $this;
    }
    public function getDiffs() {
        return $this->diffs;
    }

    public function setDiffs($value) {
        $this->diffs = $value;
        return $this;
    }
    public function getImages() {
        return $this->images;
    }

    public function setImages($value) {
        $this->images = $value;
        return $this;
    }
    public function getSvg_tag_depth() {
        return $this->svg_tag_depth;
    }

    public function setSvg_tag_depth($value) {
        $this->svg_tag_depth = $value;
        return $this;
    }
    public function getPageAnnots() {
        return $this->PageAnnots;
    }

    public function setPageAnnots($value) {
        $this->PageAnnots = $value;
        return $this;
    }
    public function getLinks() {
        return $this->links;
    }

    public function setLinks($value) {
        $this->links = $value;
        return $this;
    }
    public function getFontFamily() {
        return $this->FontFamily;
    }

    public function setFontFamily($value) {
        $this->FontFamily = $value;
        return $this;
    }
    public function getFontStyle() {
        return $this->FontStyle;
    }

    public function setFontStyle($value) {
        $this->FontStyle = $value;
        return $this;
    }
    public function getFontAscent() {
        return $this->FontAscent;
    }

    public function setFontAscent($value) {
        $this->FontAscent = $value;
        return $this;
    }
    public function getFontDescent() {
        return $this->FontDescent;
    }

    public function setFontDescent($value) {
        $this->FontDescent = $value;
        return $this;
    }
    public function getUnderline() {
        return $this->underline;
    }

    public function setUnderline($value) {
        $this->underline = $value;
        return $this;
    }
    public function getOverline() {
        return $this->overline;
    }

    public function setOverline($value) {
        $this->overline = $value;
        return $this;
    }
    public function getCurrentFont() {
        return $this->CurrentFont;
    }

    public function setCurrentFont($value) {
        $this->CurrentFont = $value;
        return $this;
    }
    public function getFontSizePt() {
        return $this->FontSizePt;
    }

    public function setFontSizePt($value) {
        $this->FontSizePt = $value;
        return $this;
    }
    public function getFontSize() {
        return $this->FontSize;
    }

    public function setFontSize($value) {
        $this->FontSize = $value;
        return $this;
    }
    public function getDrawColor() {
        return $this->DrawColor;
    }

    public function setDrawColor($value) {
        $this->DrawColor = $value;
        return $this;
    }
    public function getFillColor() {
        return $this->FillColor;
    }

    public function setFillColor($value) {
        $this->FillColor = $value;
        return $this;
    }
    public function getTextColor() {
        return $this->TextColor;
    }

    public function setTextColor($value) {
        $this->TextColor = $value;
        return $this;
    }
    public function getColorFlag() {
        return $this->ColorFlag;
    }

    public function setColorFlag($value) {
        $this->ColorFlag = $value;
        return $this;
    }
    public function getAutoPageBreak() {
        return $this->AutoPageBreak;
    }

    public function setAutoPageBreak($value) {
        $this->AutoPageBreak = $value;
        return $this;
    }
    public function getPageBreakTrigger() {
        return $this->PageBreakTrigger;
    }

    public function setPageBreakTrigger($value) {
        $this->PageBreakTrigger = $value;
        return $this;
    }
    public function getInHeader() {
        return $this->InHeader;
    }

    public function setInHeader($value) {
        $this->InHeader = $value;
        return $this;
    }
    public function getInFooter() {
        return $this->InFooter;
    }

    public function setInFooter($value) {
        $this->InFooter = $value;
        return $this;
    }
    public function getZoomMode() {
        return $this->ZoomMode;
    }

    public function setZoomMode($value) {
        $this->ZoomMode = $value;
        return $this;
    }
    public function getLayoutMode() {
        return $this->LayoutMode;
    }

    public function setLayoutMode($value) {
        $this->LayoutMode = $value;
        return $this;
    }
    public function getDocinfounicode() {
        return $this->docinfounicode;
    }

    public function setDocinfounicode($value) {
        $this->docinfounicode = $value;
        return $this;
    }
    public function getTitle() {
        return $this->title;
    }

    public function setTitle($value) {
        $this->title = $value;
        return $this;
    }
    public function getSubject() {
        return $this->subject;
    }

    public function setSubject($value) {
        $this->subject = $value;
        return $this;
    }
    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($value) {
        $this->author = $value;
        return $this;
    }
    public function getKeywords() {
        return $this->keywords;
    }

    public function setKeywords($value) {
        $this->keywords = $value;
        return $this;
    }
    public function getCreator() {
        return $this->creator;
    }

    public function setCreator($value) {
        $this->creator = $value;
        return $this;
    }
    public function getStarting_page_number() {
        return $this->starting_page_number;
    }

    public function setStarting_page_number($value) {
        $this->starting_page_number = $value;
        return $this;
    }
    public function getImg_rb_x() {
        return $this->img_rb_x;
    }

    public function setImg_rb_x($value) {
        $this->img_rb_x = $value;
        return $this;
    }
    public function getImg_rb_y() {
        return $this->img_rb_y;
    }

    public function setImg_rb_y($value) {
        $this->img_rb_y = $value;
        return $this;
    }
    public function getImgscale() {
        return $this->imgscale;
    }

    public function setImgscale($value) {
        $this->imgscale = $value;
        return $this;
    }
    public function getIsunicode() {
        return $this->isunicode;
    }

    public function setIsunicode($value) {
        $this->isunicode = $value;
        return $this;
    }
    public function getPDFVersion() {
        return $this->PDFVersion;
    }

    public function setPDFVersion($value) {
        $this->PDFVersion = $value;
        return $this;
    }
    public function getHeader_xobjid() {
        return $this->header_xobjid;
    }

    public function setHeader_xobjid($value) {
        $this->header_xobjid = $value;
        return $this;
    }
    public function getHeader_xobj_autoreset() {
        return $this->header_xobj_autoreset;
    }

    public function setHeader_xobj_autoreset($value) {
        $this->header_xobj_autoreset = $value;
        return $this;
    }
    public function getHeader_margin() {
        return $this->header_margin;
    }

    public function setHeader_margin($value) {
        $this->header_margin = $value;
        return $this;
    }
    public function getFooter_margin() {
        return $this->footer_margin;
    }

    public function setFooter_margin($value) {
        $this->footer_margin = $value;
        return $this;
    }
    public function getOriginal_lMargin() {
        return $this->original_lMargin;
    }

    public function setOriginal_lMargin($value) {
        $this->original_lMargin = $value;
        return $this;
    }
    public function getOriginal_rMargin() {
        return $this->original_rMargin;
    }

    public function setOriginal_rMargin($value) {
        $this->original_rMargin = $value;
        return $this;
    }
    public function getHeader_font() {
        return $this->header_font;
    }

    public function setHeader_font($value) {
        $this->header_font = $value;
        return $this;
    }
    public function getFooter_font() {
        return $this->footer_font;
    }

    public function setFooter_font($value) {
        $this->footer_font = $value;
        return $this;
    }
    public function getL() {
        return $this->l;
    }

    public function setL($value) {
        $this->l = $value;
        return $this;
    }
    public function getBarcode() {
        return $this->barcode;
    }

    public function setBarcode($value) {
        $this->barcode = $value;
        return $this;
    }
    public function getPrint_header() {
        return $this->print_header;
    }

    public function setPrint_header($value) {
        $this->print_header = $value;
        return $this;
    }
    public function getPrint_footer() {
        return $this->print_footer;
    }

    public function setPrint_footer($value) {
        $this->print_footer = $value;
        return $this;
    }
    public function getHeader_logo() {
        return $this->header_logo;
    }

    public function setHeader_logo($value) {
        $this->header_logo = $value;
        return $this;
    }
    public function getHeader_logo_width() {
        return $this->header_logo_width;
    }

    public function setHeader_logo_width($value) {
        $this->header_logo_width = $value;
        return $this;
    }
    public function getHeader_title() {
        return $this->header_title;
    }

    public function setHeader_title($value) {
        $this->header_title = $value;
        return $this;
    }
    public function getHeader_string() {
        return $this->header_string;
    }

    public function setHeader_string($value) {
        $this->header_string = $value;
        return $this;
    }
    public function getHeader_text_color() {
        return $this->header_text_color;
    }

    public function setHeader_text_color($value) {
        $this->header_text_color = $value;
        return $this;
    }
    public function getHeader_line_color() {
        return $this->header_line_color;
    }

    public function setHeader_line_color($value) {
        $this->header_line_color = $value;
        return $this;
    }
    public function getFooter_text_color() {
        return $this->footer_text_color;
    }

    public function setFooter_text_color($value) {
        $this->footer_text_color = $value;
        return $this;
    }
    public function getFooter_line_color() {
        return $this->footer_line_color;
    }

    public function setFooter_line_color($value) {
        $this->footer_line_color = $value;
        return $this;
    }
    public function getTxtshadow() {
        return $this->txtshadow;
    }

    public function setTxtshadow($value) {
        $this->txtshadow = $value;
        return $this;
    }
    public function getDefault_table_columns() {
        return $this->default_table_columns;
    }

    public function setDefault_table_columns($value) {
        $this->default_table_columns = $value;
        return $this;
    }
    public function getHREF() {
        return $this->HREF;
    }

    public function setHREF($value) {
        $this->HREF = $value;
        return $this;
    }
    public function getFontlist() {
        return $this->fontlist;
    }

    public function setFontlist($value) {
        $this->fontlist = $value;
        return $this;
    }
    public function getFgcolor() {
        return $this->fgcolor;
    }

    public function setFgcolor($value) {
        $this->fgcolor = $value;
        return $this;
    }
    public function getListordered() {
        return $this->listordered;
    }

    public function setListordered($value) {
        $this->listordered = $value;
        return $this;
    }
    public function getListcount() {
        return $this->listcount;
    }

    public function setListcount($value) {
        $this->listcount = $value;
        return $this;
    }
    public function getListnum() {
        return $this->listnum;
    }

    public function setListnum($value) {
        $this->listnum = $value;
        return $this;
    }
    public function getListindent() {
        return $this->listindent;
    }

    public function setListindent($value) {
        $this->listindent = $value;
        return $this;
    }
    public function getListindentlevel() {
        return $this->listindentlevel;
    }

    public function setListindentlevel($value) {
        $this->listindentlevel = $value;
        return $this;
    }
    public function getBgcolor() {
        return $this->bgcolor;
    }

    public function setBgcolor($value) {
        $this->bgcolor = $value;
        return $this;
    }
    public function getTempfontsize() {
        return $this->tempfontsize;
    }

    public function setTempfontsize($value) {
        $this->tempfontsize = $value;
        return $this;
    }
    public function getLispacer() {
        return $this->lispacer;
    }

    public function setLispacer($value) {
        $this->lispacer = $value;
        return $this;
    }
    public function getEncoding() {
        return $this->encoding;
    }

    public function setEncoding($value) {
        $this->encoding = $value;
        return $this;
    }
    public function getRtl() {
        return $this->rtl;
    }

    public function setRtl($value) {
        $this->rtl = $value;
        return $this;
    }
    public function getTmprtl() {
        return $this->tmprtl;
    }

    public function setTmprtl($value) {
        $this->tmprtl = $value;
        return $this;
    }
    public function getEncrypted() {
        return $this->encrypted;
    }

    public function setEncrypted($value) {
        $this->encrypted = $value;
        return $this;
    }
    public function getEncryptdata() {
        return $this->encryptdata;
    }

    public function setEncryptdata($value) {
        $this->encryptdata = $value;
        return $this;
    }
    public function getLast_enc_key() {
        return $this->last_enc_key;
    }

    public function setLast_enc_key($value) {
        $this->last_enc_key = $value;
        return $this;
    }
    public function getLast_enc_key_c() {
        return $this->last_enc_key_c;
    }

    public function setLast_enc_key_c($value) {
        $this->last_enc_key_c = $value;
        return $this;
    }
    public function getFile_id() {
        return $this->file_id;
    }

    public function setFile_id($value) {
        $this->file_id = $value;
        return $this;
    }
    public function getHash_key() {
        return $this->hash_key;
    }

    public function setHash_key($value) {
        $this->hash_key = $value;
        return $this;
    }
    public function getOutlines() {
        return $this->outlines;
    }

    public function setOutlines($value) {
        $this->outlines = $value;
        return $this;
    }
    public function getOutlineRoot() {
        return $this->OutlineRoot;
    }

    public function setOutlineRoot($value) {
        $this->OutlineRoot = $value;
        return $this;
    }
    public function getJavascript() {
        return $this->javascript;
    }

    public function setJavascript($value) {
        $this->javascript = $value;
        return $this;
    }
    public function getN_js() {
        return $this->n_js;
    }

    public function setN_js($value) {
        $this->n_js = $value;
        return $this;
    }
    public function getLinethrough() {
        return $this->linethrough;
    }

    public function setLinethrough($value) {
        $this->linethrough = $value;
        return $this;
    }
    public function getUr() {
        return $this->ur;
    }

    public function setUr($value) {
        $this->ur = $value;
        return $this;
    }
    public function getDpi() {
        return $this->dpi;
    }

    public function setDpi($value) {
        $this->dpi = $value;
        return $this;
    }
    public function getNewpagegroup() {
        return $this->newpagegroup;
    }

    public function setNewpagegroup($value) {
        $this->newpagegroup = $value;
        return $this;
    }
    public function getPagegroups() {
        return $this->pagegroups;
    }

    public function setPagegroups($value) {
        $this->pagegroups = $value;
        return $this;
    }
    public function getCurrpagegroup() {
        return $this->currpagegroup;
    }

    public function setCurrpagegroup($value) {
        $this->currpagegroup = $value;
        return $this;
    }
    public function getExtgstates() {
        return $this->extgstates;
    }

    public function setExtgstates($value) {
        $this->extgstates = $value;
        return $this;
    }
    public function getJpeg_quality() {
        return $this->jpeg_quality;
    }

    public function setJpeg_quality($value) {
        $this->jpeg_quality = $value;
        return $this;
    }
    public function getCell_height_ratio() {
        return $this->cell_height_ratio;
    }

    public function setCell_height_ratio($value) {
        $this->cell_height_ratio = $value;
        return $this;
    }
    public function getViewer_preferences() {
        return $this->viewer_preferences;
    }

    public function setViewer_preferences($value) {
        $this->viewer_preferences = $value;
        return $this;
    }
    public function getPageMode() {
        return $this->PageMode;
    }

    public function setPageMode($value) {
        $this->PageMode = $value;
        return $this;
    }
    public function getGradients() {
        return $this->gradients;
    }

    public function setGradients($value) {
        $this->gradients = $value;
        return $this;
    }
    public function getIntmrk() {
        return $this->intmrk;
    }

    public function setIntmrk($value) {
        $this->intmrk = $value;
        return $this;
    }
    public function getBordermrk() {
        return $this->bordermrk;
    }

    public function setBordermrk($value) {
        $this->bordermrk = $value;
        return $this;
    }
    public function getEmptypagemrk() {
        return $this->emptypagemrk;
    }

    public function setEmptypagemrk($value) {
        $this->emptypagemrk = $value;
        return $this;
    }
    public function getCntmrk() {
        return $this->cntmrk;
    }

    public function setCntmrk($value) {
        $this->cntmrk = $value;
        return $this;
    }
    public function getFooterpos() {
        return $this->footerpos;
    }

    public function setFooterpos($value) {
        $this->footerpos = $value;
        return $this;
    }
    public function getFooterlen() {
        return $this->footerlen;
    }

    public function setFooterlen($value) {
        $this->footerlen = $value;
        return $this;
    }
    public function getNewline() {
        return $this->newline;
    }

    public function setNewline($value) {
        $this->newline = $value;
        return $this;
    }
    public function getEndlinex() {
        return $this->endlinex;
    }

    public function setEndlinex($value) {
        $this->endlinex = $value;
        return $this;
    }
    public function getLinestyleWidth() {
        return $this->linestyleWidth;
    }

    public function setLinestyleWidth($value) {
        $this->linestyleWidth = $value;
        return $this;
    }
    public function getLinestyleCap() {
        return $this->linestyleCap;
    }

    public function setLinestyleCap($value) {
        $this->linestyleCap = $value;
        return $this;
    }
    public function getLinestyleJoin() {
        return $this->linestyleJoin;
    }

    public function setLinestyleJoin($value) {
        $this->linestyleJoin = $value;
        return $this;
    }
    public function getLinestyleDash() {
        return $this->linestyleDash;
    }

    public function setLinestyleDash($value) {
        $this->linestyleDash = $value;
        return $this;
    }
    public function getOpenMarkedContent() {
        return $this->openMarkedContent;
    }

    public function setOpenMarkedContent($value) {
        $this->openMarkedContent = $value;
        return $this;
    }
    public function getHtmlvspace() {
        return $this->htmlvspace;
    }

    public function setHtmlvspace($value) {
        $this->htmlvspace = $value;
        return $this;
    }
    public function getSpot_colors() {
        return $this->spot_colors;
    }

    public function setSpot_colors($value) {
        $this->spot_colors = $value;
        return $this;
    }
    public function getLisymbol() {
        return $this->lisymbol;
    }

    public function setLisymbol($value) {
        $this->lisymbol = $value;
        return $this;
    }

    public function getTransfmatrix() {
        return $this->transfmatrix;
    }

    public function setTransfmatrix($value) {
        $this->transfmatrix = $value;
        return $this;
    }
    public function getTransfmatrix_key() {
        return $this->transfmatrix_key;
    }

    public function setTransfmatrix_key($value) {
        $this->transfmatrix_key = $value;
        return $this;
    }
    public function getBooklet() {
        return $this->booklet;
    }

    public function setBooklet($value) {
        $this->booklet = $value;
        return $this;
    }
    public function getFeps() {
        return $this->feps;
    }

    public function setFeps($value) {
        $this->feps = $value;
        return $this;
    }
    public function getTagvspaces() {
        return $this->tagvspaces;
    }

    public function setTagvspaces($value) {
        $this->tagvspaces = $value;
        return $this;
    }
    public function getCustomlistindent() {
        return $this->customlistindent;
    }

    public function setCustomlistindent($value) {
        $this->customlistindent = $value;
        return $this;
    }
    public function getOpencell() {
        return $this->opencell;
    }

    public function setOpencell($value) {
        $this->opencell = $value;
        return $this;
    }
    public function getEmbeddedfiles() {
        return $this->embeddedfiles;
    }

    public function setEmbeddedfiles($value) {
        $this->embeddedfiles = $value;
        return $this;
    }
    public function getPremode() {
        return $this->premode;
    }

    public function setPremode($value) {
        $this->premode = $value;
        return $this;
    }
    public function getTransfmrk() {
        return $this->transfmrk;
    }

    public function setTransfmrk($value) {
        $this->transfmrk = $value;
        return $this;
    }
    public function getHtmlLinkColorArray() {
        return $this->htmlLinkColorArray;
    }

    public function setHtmlLinkColorArray($value) {
        $this->htmlLinkColorArray = $value;
        return $this;
    }
    public function getHtmlLinkFontStyle() {
        return $this->htmlLinkFontStyle;
    }

    public function setHtmlLinkFontStyle($value) {
        $this->htmlLinkFontStyle = $value;
        return $this;
    }
    public function getNumpages() {
        return $this->numpages;
    }

    public function setNumpages($value) {
        $this->numpages = $value;
        return $this;
    }
    public function getPagelen() {
        return $this->pagelen;
    }

    public function setPagelen($value) {
        $this->pagelen = $value;
        return $this;
    }
    public function getNumimages() {
        return $this->numimages;
    }

    public function setNumimages($value) {
        $this->numimages = $value;
        return $this;
    }

    public function getBufferlen() {
        return $this->bufferlen;
    }

    public function setBufferlen($value) {
        $this->bufferlen = $value;
        return $this;
    }
    public function getNumfonts() {
        return $this->numfonts;
    }

    public function setNumfonts($value) {
        $this->numfonts = $value;
        return $this;
    }
    public function getFontkeys() {
        return $this->fontkeys;
    }

    public function setFontkeys($value) {
        $this->fontkeys = $value;
        return $this;
    }
    public function getFont_obj_ids() {
        return $this->font_obj_ids;
    }

    public function setFont_obj_ids($value) {
        $this->font_obj_ids = $value;
        return $this;
    }
    public function getPageopen() {
        return $this->pageopen;
    }

    public function setPageopen($value) {
        $this->pageopen = $value;
        return $this;
    }
    public function getDefault_monospaced_font() {
        return $this->default_monospaced_font;
    }

    public function setDefault_monospaced_font($value) {
        $this->default_monospaced_font = $value;
        return $this;
    }
    public function getObjcopy() {
        return $this->objcopy;
    }

    public function setObjcopy($value) {
        $this->objcopy = $value;
        return $this;
    }
    public function getCache_file_length() {
        return $this->cache_file_length;
    }

    public function setCache_file_length($value) {
        $this->cache_file_length = $value;
        return $this;
    }
    public function getThead() {
        return $this->thead;
    }

    public function setThead($value) {
        $this->thead = $value;
        return $this;
    }
    public function getTheadMargins() {
        return $this->theadMargins;
    }

    public function setTheadMargins($value) {
        $this->theadMargins = $value;
        return $this;
    }
    public function getSign() {
        return $this->sign;
    }

    public function setSign($value) {
        $this->sign = $value;
        return $this;
    }
    public function getSignature_data() {
        return $this->signature_data;
    }

    public function setSignature_data($value) {
        $this->signature_data = $value;
        return $this;
    }
    public function getSignature_max_length() {
        return $this->signature_max_length;
    }

    public function setSignature_max_length($value) {
        $this->signature_max_length = $value;
        return $this;
    }
    public function getSignature_appearance() {
        return $this->signature_appearance;
    }

    public function setSignature_appearance($value) {
        $this->signature_appearance = $value;
        return $this;
    }
    public function getEmpty_signature_appearance() {
        return $this->empty_signature_appearance;
    }

    public function setEmpty_signature_appearance($value) {
        $this->empty_signature_appearance = $value;
        return $this;
    }
    public function getTsa_timestamp() {
        return $this->tsa_timestamp;
    }

    public function setTsa_timestamp($value) {
        $this->tsa_timestamp = $value;
        return $this;
    }
    public function getTsa_data() {
        return $this->tsa_data;
    }

    public function setTsa_data($value) {
        $this->tsa_data = $value;
        return $this;
    }
    public function getRe_spaces() {
        return $this->re_spaces;
    }

    public function setRe_spaces($value) {
        $this->re_spaces = $value;
        return $this;
    }
    public function getRe_space() {
        return $this->re_space;
    }

    public function setRe_space($value) {
        $this->re_space = $value;
        return $this;
    }
    public function getSig_obj_id() {
        return $this->sig_obj_id;
    }

    public function setSig_obj_id($value) {
        $this->sig_obj_id = $value;
        return $this;
    }
    public function getPage_obj_id() {
        return $this->page_obj_id;
    }

    public function setPage_obj_id($value) {
        $this->page_obj_id = $value;
        return $this;
    }
    public function getForm_obj_id() {
        return $this->form_obj_id;
    }

    public function setForm_obj_id($value) {
        $this->form_obj_id = $value;
        return $this;
    }
    public function getDefault_form_prop() {
        return $this->default_form_prop;
    }

    public function setDefault_form_prop($value) {
        $this->default_form_prop = $value;
        return $this;
    }
    public function getJs_objects() {
        return $this->js_objects;
    }

    public function setJs_objects($value) {
        $this->js_objects = $value;
        return $this;
    }
    public function getForm_action() {
        return $this->form_action;
    }

    public function setForm_action($value) {
        $this->form_action = $value;
        return $this;
    }
    public function getForm_enctype() {
        return $this->form_enctype;
    }

    public function setForm_enctype($value) {
        $this->form_enctype = $value;
        return $this;
    }
    public function getForm_mode() {
        return $this->form_mode;
    }

    public function setForm_mode($value) {
        $this->form_mode = $value;
        return $this;
    }
    public function getAnnotation_fonts() {
        return $this->annotation_fonts;
    }

    public function setAnnotation_fonts($value) {
        $this->annotation_fonts = $value;
        return $this;
    }
    public function getRadiobutton_groups() {
        return $this->radiobutton_groups;
    }

    public function setRadiobutton_groups($value) {
        $this->radiobutton_groups = $value;
        return $this;
    }
    public function getRadio_groups() {
        return $this->radio_groups;
    }

    public function setRadio_groups($value) {
        $this->radio_groups = $value;
        return $this;
    }
    public function getTextindent() {
        return $this->textindent;
    }

    public function setTextindent($value) {
        $this->textindent = $value;
        return $this;
    }
    public function getStart_transaction_page() {
        return $this->start_transaction_page;
    }

    public function setStart_transaction_page($value) {
        $this->start_transaction_page = $value;
        return $this;
    }
    public function getStart_transaction_y() {
        return $this->start_transaction_y;
    }

    public function setStart_transaction_y($value) {
        $this->start_transaction_y = $value;
        return $this;
    }
    public function getInthead() {
        return $this->inthead;
    }

    public function setInthead($value) {
        $this->inthead = $value;
        return $this;
    }
    public function getColumns() {
        return $this->columns;
    }

    public function setColumns($value) {
        $this->columns = $value;
        return $this;
    }
    public function getNum_columns() {
        return $this->num_columns;
    }

    public function setNum_columns($value) {
        $this->num_columns = $value;
        return $this;
    }
    public function getCurrent_column() {
        return $this->current_column;
    }

    public function setCurrent_column($value) {
        $this->current_column = $value;
        return $this;
    }
    public function getColumn_start_page() {
        return $this->column_start_page;
    }

    public function setColumn_start_page($value) {
        $this->column_start_page = $value;
        return $this;
    }
    public function getMaxselcol() {
        return $this->maxselcol;
    }

    public function setMaxselcol($value) {
        $this->maxselcol = $value;
        return $this;
    }
    public function getColxshift() {
        return $this->colxshift;
    }

    public function setColxshift($value) {
        $this->colxshift = $value;
        return $this;
    }
    public function getTextrendermode() {
        return $this->textrendermode;
    }

    public function setTextrendermode($value) {
        $this->textrendermode = $value;
        return $this;
    }
    public function getTextstrokewidth() {
        return $this->textstrokewidth;
    }

    public function setTextstrokewidth($value) {
        $this->textstrokewidth = $value;
        return $this;
    }
    public function getStrokecolor() {
        return $this->strokecolor;
    }

    public function setStrokecolor($value) {
        $this->strokecolor = $value;
        return $this;
    }
    public function getPdfunit() {
        return $this->pdfunit;
    }

    public function setPdfunit($value) {
        $this->pdfunit = $value;
        return $this;
    }
    public function getTocpage() {
        return $this->tocpage;
    }

    public function setTocpage($value) {
        $this->tocpage = $value;
        return $this;
    }
    public function getRasterize_vector_images() {
        return $this->rasterize_vector_images;
    }

    public function setRasterize_vector_images($value) {
        $this->rasterize_vector_images = $value;
        return $this;
    }
    public function getFont_subsetting() {
        return $this->font_subsetting;
    }

    public function setFont_subsetting($value) {
        $this->font_subsetting = $value;
        return $this;
    }
    public function getDefault_graphic_vars() {
        return $this->default_graphic_vars;
    }

    public function setDefault_graphic_vars($value) {
        $this->default_graphic_vars = $value;
        return $this;
    }
    public function getXobjects() {
        return $this->xobjects;
    }

    public function setXobjects($value) {
        $this->xobjects = $value;
        return $this;
    }
    public function getInxobj() {
        return $this->inxobj;
    }

    public function setInxobj($value) {
        $this->inxobj = $value;
        return $this;
    }
    public function getXobjid() {
        return $this->xobjid;
    }

    public function setXobjid($value) {
        $this->xobjid = $value;
        return $this;
    }
    public function getFont_stretching() {
        return $this->font_stretching;
    }

    public function setFont_stretching($value) {
        $this->font_stretching = $value;
        return $this;
    }
    public function getFont_spacing() {
        return $this->font_spacing;
    }

    public function setFont_spacing($value) {
        $this->font_spacing = $value;
        return $this;
    }
    public function getPage_regions() {
        return $this->page_regions;
    }

    public function setPage_regions($value) {
        $this->page_regions = $value;
        return $this;
    }
    public function getCheck_page_regions() {
        return $this->check_page_regions;
    }

    public function setCheck_page_regions($value) {
        $this->check_page_regions = $value;
        return $this;
    }
    public function getPdflayers() {
        return $this->pdflayers;
    }

    public function setPdflayers($value) {
        $this->pdflayers = $value;
        return $this;
    }
    public function getDests() {
        return $this->dests;
    }

    public function setDests($value) {
        $this->dests = $value;
        return $this;
    }
    public function getN_dests() {
        return $this->n_dests;
    }

    public function setN_dests($value) {
        $this->n_dests = $value;
        return $this;
    }
    public function getEfnames() {
        return $this->efnames;
    }

    public function setEfnames($value) {
        $this->efnames = $value;
        return $this;
    }
    public function getSvgdir() {
        return $this->svgdir;
    }

    public function setSvgdir($value) {
        $this->svgdir = $value;
        return $this;
    }
    public function getSvgunit() {
        return $this->svgunit;
    }

    public function setSvgunit($value) {
        $this->svgunit = $value;
        return $this;
    }
    public function getSvggradients() {
        return $this->svggradients;
    }

    public function setSvggradients($value) {
        $this->svggradients = $value;
        return $this;
    }
    public function getSvggradientid() {
        return $this->svggradientid;
    }

    public function setSvggradientid($value) {
        $this->svggradientid = $value;
        return $this;
    }
    public function getSvgdefsmode() {
        return $this->svgdefsmode;
    }

    public function setSvgdefsmode($value) {
        $this->svgdefsmode = $value;
        return $this;
    }
    public function getSvgdefs() {
        return $this->svgdefs;
    }

    public function setSvgdefs($value) {
        $this->svgdefs = $value;
        return $this;
    }
    public function getSvgclipmode() {
        return $this->svgclipmode;
    }

    public function setSvgclipmode($value) {
        $this->svgclipmode = $value;
        return $this;
    }
    public function getSvgclippaths() {
        return $this->svgclippaths;
    }

    public function setSvgclippaths($value) {
        $this->svgclippaths = $value;
        return $this;
    }
    public function getSvgcliptm() {
        return $this->svgcliptm;
    }

    public function setSvgcliptm($value) {
        $this->svgcliptm = $value;
        return $this;
    }
    public function getSvgclipid() {
        return $this->svgclipid;
    }

    public function setSvgclipid($value) {
        $this->svgclipid = $value;
        return $this;
    }
    public function getSvgtext() {
        return $this->svgtext;
    }

    public function setSvgtext($value) {
        $this->svgtext = $value;
        return $this;
    }
    public function getSvgtextmode() {
        return $this->svgtextmode;
    }

    public function setSvgtextmode($value) {
        $this->svgtextmode = $value;
        return $this;
    }
    public function getSvgstyles() {
        return $this->svgstyles;
    }

    public function setSvgstyles($value) {
        $this->svgstyles = $value;
        return $this;
    }
    public function getForce_srgb() {
        return $this->force_srgb;
    }

    public function setForce_srgb($value) {
        $this->force_srgb = $value;
        return $this;
    }

    public function getPdfa_version() {
        return $this->pdfa_version;
    }

    public function setPdfa_version($value) {
        $this->pdfa_version = $value;
        return $this;
    }
    public function getDoc_creation_timestamp() {
        return $this->doc_creation_timestamp;
    }

    public function setDoc_creation_timestamp($value) {
        $this->doc_creation_timestamp = $value;
        return $this;
    }
    public function getDoc_modification_timestamp() {
        return $this->doc_modification_timestamp;
    }

    public function setDoc_modification_timestamp($value) {
        $this->doc_modification_timestamp = $value;
        return $this;
    }
    public function getCustom_xmp() {
        return $this->custom_xmp;
    }

    public function setCustom_xmp($value) {
        $this->custom_xmp = $value;
        return $this;
    }
    public function getCustom_xmp_rdf() {
        return $this->custom_xmp_rdf;
    }

    public function setCustom_xmp_rdf($value) {
        $this->custom_xmp_rdf = $value;
        return $this;
    }
    public function getCustom_xmp_rdf_pdfaExtension() {
        return $this->custom_xmp_rdf_pdfaExtension;
    }

    public function setCustom_xmp_rdf_pdfaExtension($value) {
        $this->custom_xmp_rdf_pdfaExtension = $value;
        return $this;
    }
    public function getOverprint() {
        return $this->overprint;
    }

    public function setOverprint($value) {
        $this->overprint = $value;
        return $this;
    }
    public function getAlpha() {
        return $this->alpha;
    }

    public function setAlpha($value) {
        $this->alpha = $value;
        return $this;
    }
    public function getPage_boxes() {
        return $this->page_boxes;
    }

    public function setPage_boxes($value) {
        $this->page_boxes = $value;
        return $this;
    }
    public function getTcpdflink() {
        return $this->tcpdflink;
    }

    public function setTcpdflink($value) {
        $this->tcpdflink = $value;
        return $this;
    }
    public function getGdgammacache() {
        return $this->gdgammacache;
    }

    public function setGdgammacache($value) {
        $this->gdgammacache = $value;
        return $this;
    }
    public function getFileContentCache() {
        return $this->fileContentCache;
    }

    public function setFileContentCache($value) {
        $this->fileContentCache = $value;
        return $this;
    }
    public function getAllowLocalFiles() {
        return $this->allowLocalFiles;
    }

    public function setAllowLocalFiles($value) {
        $this->allowLocalFiles = $value;
        return $this;
    }

}
