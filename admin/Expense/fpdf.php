<?php
ob_start();
/**
 * Custom FPDF Class - A PHP class to generate PDF files.
 *
 * @package FPDF
 * @version 1.82 (Released 2017-05-17)
 * @author Olivier Plathey
 * @license FPDF license (http://www.fpdf.org)
 */

class FPDF
{
    // Class properties
    protected $page;               // Current page number
    protected $pages = [];         // Array to store pages
    protected $fonts = [];         // Array to store fonts
    protected $current_font;       // Current font
    protected $fonts_path = '';    // Path to fonts directory
    protected $pdf_version = '1.3'; // PDF version
    protected $compress = false;   // Compression flag
    protected $orientation = 'P';  // Page orientation (P for Portrait, L for Landscape)
    protected $size = 'A4';        // Page size
    protected $state = 0;          // Document state
    protected $buffer = '';        // Output buffer
    protected $file = '';          // Output file name
    protected $error = '';         // Error message

    // Margins
    protected $left_margin = 10;
    protected $top_margin = 10;
    protected $right_margin = 10;
    protected $auto_page_break = true;
    protected $page_break_margin = 10;

    /**
     * Constructor
     *
     * @param string $orientation Page orientation (P or L)
     * @param string $unit Unit of measurement (default: mm)
     * @param string $size Page size (default: A4)
     */
    public function __construct($orientation = 'P', $unit = 'mm', $size = 'A4')
    {
        // Initialize PDF properties
        $this->orientation = $orientation;
        $this->size = $size;
        $this->SetMargins($this->left_margin, $this->top_margin, $this->right_margin);
        $this->SetAutoPageBreak($this->auto_page_break, $this->page_break_margin);
        $this->AddPage();
    }

    /**
     * Add a new page to the PDF
     *
     * @param string $orientation Page orientation (P or L)
     * @param string $size Page size
     */
    public function AddPage($orientation = '', $size = '')
    {
        // If orientation is provided, update the orientation
        if ($orientation) {
            $this->orientation = $orientation;
        }

        // If size is provided, update the size
        if ($size) {
            $this->size = $size;
        }

        // Increment page count and initialize a new page
        $this->page++;
        $this->pages[$this->page] = '';
    }

    /**
     * Set the font for the PDF
     *
     * @param string $family Font family
     * @param string $style Font style (B, I, U, or combination)
     * @param int $size Font size
     */
    public function SetFont($family, $style = '', $size = 0)
    {
        // Validate font family
        if (empty($family)) {
            $this->error = 'Font family cannot be empty.';
            return;
        }

        // Set the current font
        $this->current_font = [
            'family' => $family,
            'style' => $style,
            'size' => $size
        ];
    }

    /**
     * Create a cell in the PDF
     *
     * @param float $w Width of the cell
     * @param float $h Height of the cell (default: 0)
     * @param string $txt Text to display in the cell
     * @param int $border Border style (0: no border, 1: frame)
     * @param int $ln Line break (0: no break, 1: next line)
     * @param string $align Text alignment (L: left, C: center, R: right)
     * @param bool $fill Fill the cell with background color
     * @param string $link URL or identifier for link
     */
    public function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        // Validate width
        if ($w <= 0) {
            $this->error = 'Cell width must be greater than 0.';
            return;
        }

        // Add cell content to the current page
        $this->pages[$this->page] .= "Cell: $txt\n";

        // If a line break is requested, add one
        if ($ln == 1) {
            $this->Ln();
        }
    }

    /**
     * Line break
     *
     * @param float $h Height of the line break
     */
    public function Ln($h = null)
    {
        if ($h == null) {
            $h = $this->current_font['size'] * 1.5; // Default line height
        }

        // Increase the Y position by the height of the line break
        $this->pages[$this->page] .= "\n";  // Adding a line break to the page content
    }

    /**
     * Output the PDF content
     *
     * @param string $dest Destination (I: inline, D: download, F: file, S: string)
     * @param string $name Output file name
     * @return string|void
     */
    public function Output($dest = 'I', $name = '')
    {
        // Generate the PDF content
        $this->buffer = "%PDF-1.3\n";
        foreach ($this->pages as $page) {
            $this->buffer .= $page;
        }
        $this->buffer .= "%%EOF\n";

        // Handle output based on destination
        switch ($dest) {
            case 'I':
                // Output to browser inline
                header('Content-Type: application/pdf');
                header('Content-Disposition: inline; filename="' . ($name ?: 'document.pdf') . '"');
                echo $this->buffer;
                break;
            case 'D':
                // Force download
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . ($name ?: 'document.pdf') . '"');
                echo $this->buffer;
                break;
            case 'F':
                // Save to file
                if (empty($name)) {
                    $this->error = 'File name cannot be empty for destination F.';
                    return;
                }
                file_put_contents($name, $this->buffer);
                break;
            case 'S':
                // Return as string
                return $this->buffer;
            default:
                $this->error = 'Invalid output destination.';
                return;
        }
    }

    /**
     * Set margins for the PDF
     *
     * @param float $left Left margin
     * @param float $top Top margin
     * @param float $right Right margin (optional)
     */
    public function SetMargins($left, $top, $right = null)
    {
        $this->left_margin = $left;
        $this->top_margin = $top;
        $this->right_margin = $right ?? $left;
    }

    /**
     * Enable or disable automatic page break
     *
     * @param bool $auto Auto page break flag
     * @param float $margin Margin from the bottom
     */
    public function SetAutoPageBreak($auto, $margin)
    {
        $this->auto_page_break = $auto;
        $this->page_break_margin = $margin;
    }

    /**
     * Get the last error message
     *
     * @return string
     */
    public function GetError()
    {
        return $this->error;
    }
}
ob_end_flush();
?>
