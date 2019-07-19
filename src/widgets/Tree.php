<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use eseperio\admintheme\helpers\Html;
use yii\base\Widget;

class Tree extends Widget
{
    public $nodes = [];

    public function run()
    {
        $nodes = $this->buildTree($this->nodes);
        $out = Html::beginTag('ul');
        $nodeDepth = $currDepth = $counter = 0;

        foreach ($nodes as $node) {
            $nodeOptions = ['class' => ''];
            if ($nodeDepth == $currDepth) {
                if ($counter > 0) {
                    $out .= "</li>\n";
                }
            } elseif ($nodeDepth > $currDepth) {
                $out .= Html::beginTag('ul', ['class' => '']) . "\n";
                $currDepth = $currDepth + ($nodeDepth - $currDepth);
            } elseif ($nodeDepth < $currDepth) {
                $out .= str_repeat("</li>\n</ul>", $currDepth - $nodeDepth) . "</li>\n";
                $currDepth = $currDepth - ($currDepth - $nodeDepth);
            }


            $nodeDepth = $node['depth'];
            $nodeName = $node['name'];

            $css = [];

            if (!empty($css)) {
                Html::addCssClass($nodeOptions, $css);
            }

            $out .= Html::beginTag('li', $nodeOptions) . "\n";
            $out .= IconDisplay::widget(['model' => $node]) . " ";
            $out .= Html::a(Html::encode($nodeName), ['properties', 'uuid' => $node['uuid']], ['class' => 'node-label']) . "\n";


            ++$counter;

        }
        $out .= str_repeat("</li>\n</ul>", $nodeDepth) . "</li>\n";
        $out .= "</ul>\n";

        return $out;

    }

    private function buildTree(array $elements, $parentId = 0, &$depth = 0)
    {
        $branch = [];

        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                ++$depth;
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }
                $element['depth']=$depth;
                $branch[] = $element;
            }
            $depth--;
        }

        return $branch;
    }
}
